<?php
/* TODO: populate all results with the dynamic data */
$post_id = $_GET['post'];
if (function_exists('get_fields')) {
    $fields = get_fields($post_id);
}

$fullname = 'U Thura Aung';
$father = 'Father name here';
$dob = '11/11/1900';
$nrc = '12/AAAAAA(N)123456';
$reason = "i want to make some money transfer easily.. i want to make some money transfer easily..  i want to make some money transfer easily..";
$multiline_text = "AABB CC DD EE FFFF GGG AABB C";
?>
<?php
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xml:lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Account Opening Form</title>
    <link href="<?php echo plugins_url('templates/style.css', MCB_PDF_PLUGIN_BASENAME) ?>" rel="stylesheet" media="all" />
</head>

<body>
    <fieldset>
        <legend>Personal Information</legend>
        <table>
            <tbody>
                <tr>
                    <th>Saluation*</th>
                    <td>
                        <div class="form-check">
                            <input type="radio" /><label>Mr</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Mrs</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Ms</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Dr</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Prof</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Other Please Specify</label>
                            <span class="underline"><?php echo 'Value here'; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Full Name & Alias*</th>
                    <td><?php echo make_letter_block($fullname); ?></td>
                </tr>
                <tr>
                    <th>Father's Name*</th>
                    <td><?php echo make_letter_block($father); ?></td>
                </tr>
                <tr>
                    <th>Date of Birth*</th>
                    <td><?php echo make_letter_block($dob, '/', 10); ?></td>
                </tr>
                <tr>
                    <th>NRC*</th>
                    <td><?php echo make_letter_block($nrc); ?></td>
                </tr>
                <tr>
                    <th>Purpose of Account Opening*</th>
                    <td>
                        <p class="text"><span class="underline"><?php echo nl2br($reason); ?></p>
                    </td>
                </tr>
                <tr>
                    <th>Company Code/Group Code*</th>
                    <td><?php echo make_letter_block('ABCDE3', '', 10); ?></td>
                </tr>
                <tr>
                    <th>Martial Status</th>
                    <td>
                        <div class="form-check">
                            <input type="radio" /><label>Single</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Married</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Separated</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Divorced</label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset>
        <legend>Minor Accounts</legend>
        <table>
            <tbody>
                <tr>
                    <td colspan="2">
                        <label>Is the application a Minor (less than 18 years of age)*</label>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Yes</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>No</label>
                        </div>

                    </td>
                </tr>
                <tr>
                    <th>Full Name of Guardian*</th>
                    <td><?php echo make_letter_block('full name of guardian'); ?></td>
                </tr>
                <tr>
                    <th>NRC of Guardian*</th>
                    <td><?php echo make_letter_block('11/AABBCC(N)456789'); ?></td>
                </tr>
                <tr>
                    <th>Date of Maturity as Minor*</th>
                    <td><?php echo make_letter_block('01/01/1900', '/', 10); ?></td>
                </tr>
                <tr>
                    <th>Martial Status</th>
                    <td>
                        <div class="form-check">
                            <input type="radio" /><label>Single</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Married</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Separated</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Divorced</label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="page-break"></div>
    <fieldset>
        <legend>Minor Accounts</legend>
        <table>
            <tbody>
                <tr>
                    <td colspan="2">
                        <label>Is the application a Minor (less than 18 years of age)*</label>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Yes</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>No</label>
                        </div>

                    </td>
                </tr>
                <tr>
                    <th>Full Name of Guardian*</th>
                    <td><?php echo make_letter_block('full name of guardian'); ?></td>
                </tr>
                <tr>
                    <th>NRC of Guardian*</th>
                    <td><?php echo make_letter_block('11/AABBCC(N)456789'); ?></td>
                </tr>
                <tr>
                    <th>Date of Maturity as Minor*</th>
                    <td><?php echo make_letter_block('01/01/1900', '/', 10); ?></td>
                </tr>
                <tr>
                    <th>Martial Status</th>
                    <td>
                        <div class="form-check">
                            <input type="radio" /><label>Single</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Married</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Separated</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Divorced</label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset>
        <legend>Occupation/Employment Details</legend>
        <table>
            <tbody>
                <tr>
                    <th>Occupation*</th>
                    <td>
                        <div class="form-check">
                            <input type="radio" /><label>Salaried</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" checked="checked" /><label>Self Employed</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Homemaker</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Retired</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Agriculturist</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" /><label>Student</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <td>
                        <div class="form-check w-full">
                            <input type="radio" />
                            <label>Other Please Specify</label>
                            <span class="underline"><?php echo 'Value here'; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Business Office Address</th>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Flat/Building/House No</th>
                    <td><?php echo make_letter_block(''); ?></td>
                </tr>
                <tr>
                    <th>Road No/Name</th>
                    <td><?php echo make_letter_block(''); ?></td>
                </tr>
                <tr>
                    <th>Landmark</th>
                    <td><?php echo make_letter_block(''); ?></td>
                </tr>

            </tbody>
        </table>
    </fieldset>

    <fieldset>
        <legend>Risking Profiling</legend>
        <table>
            <tbody>

                <tr>
                    <th>Introducer 1*</th>
                    <td class="w-sm">Full Name of Introducer</td>
                    <td>
                        <?php echo make_letter_block($multiline_text, '', 20); ?>
                    </td>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <td class="w-sm">Full Name of Introducer</td>
                    <td><?php echo make_letter_block('', '', 20); ?></td>
                </tr>


            </tbody>
        </table>
    </fieldset>
</body>

</html>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
