<?php
/*
Plugin Name: MCB - Generate PDF
Plugin URI: https://mcb.com.mm
Description: It generates PDF of the forms including on-boarding, account opening fomrs for individual and company.
Version: 1.0
Author: Digital Dots
Author URI: https://digitaldots.com.mm
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('MCB_PDF_PLUGIN')) {
    define('MCB_PDF_PLUGIN', __FILE__);
    define('MCB_PDF_PLUGIN_BASENAME', plugin_basename(MCB_PDF_PLUGIN));
    define('MCB_PDF_PLUGIN_DIR', untrailingslashit(dirname(MCB_PDF_PLUGIN)));
    // add the list of post types where you want this pdf print function to work
    define('MCB_ALLOWED_POST_TYPES',  [
        'post',
    ]);
}

// we only want to run for admin section
if (!is_admin()) return;

// if composer is not running yet, do nothing
if (!file_exists(__DIR__ . '/vendor/autoload.php')) return;

require_once __DIR__ . '/vendor/autoload.php';

//if dompdf class is not exist, do nothing
if (!class_exists('Dompdf\Dompdf')) return;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Enqueue the admin js script
 */
add_action('admin_enqueue_scripts',  'mcb_pdf_admin_scripts_and_styles', 100);

function mcb_pdf_admin_scripts_and_styles()
{
    global $post;

    //load script into the allowed post type
    if (isset($post) && mcb_pdf_is_allowed_post_type($post->post_type)) {
        wp_enqueue_script(
            'mcbpdf_admin',
            plugins_url('js/admin.js', MCB_PDF_PLUGIN_BASENAME),
            array('jquery'),
            time(),
            true
        );
    }
}

add_action('admin_init', 'mcb_pdf_exec', 98);

function mcb_pdf_exec()
{
    if (current_user_can('manage_options') && isset($_GET['output']) && $_GET['output'] == 'pdf') {

        // no post is found, do nothing
        if (!isset($_GET['post'])) return;

        // not allowed post type, do nothing
        $post_type = get_post_type($_GET['post']);
        if (!mcb_pdf_is_allowed_post_type($post_type)) return;

        $content =  include_once('templates/account-opening-individual.php');
        
        // attaching &html=yes to the url will show you the page on the browser
        if (isset($_GET['html']) && $_GET['html'] == '1') {
            echo $content;
            exit;
        } else {
            pdf_output($content);
        }
    }
}

function pdf_output($content)
{
    $options = new Options();
    $options->set('A4', 'potrait');
    $options->set('enable_css_float', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    /* this will be dynamic */
    $filename = "account_opening-" . time();

    // instantiate and use the dompdf class
    $dompdf = new Dompdf($options);
    //    $content = '<h1>234234</h1>';
    $dompdf->loadHtml($content);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    $font = $dompdf->getFontMetrics()->getFont("Arial", "bold");
    $dompdf->getCanvas()->page_text(270, 820, "Page: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0, 0, 0));

    // save to the server
    // $output = $dompdf->output();
    // file_put_contents("file.pdf", $output);

    // Save PDF file
    //$dompdf->stream($filename);

    // Output PDF to the browser to see the preview
    $dompdf->stream($filename, array("Attachment" => 0));
    exit;
}

/**
 * To make each word as block, join them and format it
 * 
 * @param string $word The words to be processed as block
 * @param string $separator An optional separator to be used to join each letter
 * @param int $blocks_per_line The maximum number of blocks to be filled on each line
 * @param int $num_of_lines The maxium number of lines to be marked up
 * 
 * @return string 
 */

function make_letter_block($word, $separator = '', $blocks_per_line = 27)
{
    $final_word     = array();
    $result         = array();
    $word           = strtoupper(trim($word));
    $total_chars    = strlen($word);
    $max_blocks     = $blocks_per_line;

    if (empty($separator)) {
        $multiple_words = explode(' ', $word);
    } else {
        $multiple_words = explode($separator, $word);
    }

    foreach ($multiple_words as $word) {

        $chars = str_split($word);

        foreach ($chars as $c) {
            $final_word[] = html_letter_block($c);
        }
        if (!empty($separator)) {
            $final_word[] = html_letter_block($separator, false);
        } else {
            $final_word[] = html_letter_block('');
        }
    }
    // we don't want the last separator - e.g dob: 01/01/1900/ - remove the last slash
    array_pop($final_word);

    // what if number of characters is more than number of blocks per line
    // we need to make extra line of blocks
    if ($total_chars > 0 && $total_chars > $blocks_per_line) {

        $total_lines = ceil($total_chars / $blocks_per_line);
        $max_blocks = $total_lines * $blocks_per_line;

        // chunk the array based on the number of blocks per line
        $chunk_array = array_chunk($final_word, $blocks_per_line);
        foreach ($chunk_array as  $chunk) {

            // fill up the empty block to look nicer on UI
            if (count($chunk) < $blocks_per_line) {
                $filled = array_pad($chunk, $blocks_per_line, html_letter_block(''));
            } else {
                $filled  = $chunk;
            }

            // hack - to break each line, dompdf doesn't like css float
            array_unshift($filled, '<div>');
            $filled[] = '</div>';

            $result = array_merge($result, $filled);
        }
    } else {
        // fill up the empty block to look nicer on UI
        $result = array_pad($final_word, $max_blocks, html_letter_block(''));
    }

    return join('', $result);
}
/**
 * To make each letter as block and return it
 * 
 * @param string $letter The char to be returned as block
 * @param bool $is_border Check if the block needs the border
 * @return string
 */

function html_letter_block($letter = '', $is_border = true)
{
    $border_class = !$is_border ? 'no-border' : '';
    $block = "
        <div class='block $border_class'>{$letter}</div>
    ";
    return $block;
}

function mcb_pdf_is_allowed_post_type($pt)
{
    if (in_array($pt, MCB_ALLOWED_POST_TYPES)) {
        return true;
    }
    return false;
}
