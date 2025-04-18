(function($) {
  $.fn.ajaxLoader = function(options) {
    // Default options
    const settings = $.extend({
      loadMoreButton: '#load-more', // The button to load more posts     
      ajaxUrl: rugbySettings.ajaxUrl, 
      loadingIcon: '<span class="ajax-loading-icon"></span>', // The small loading icon HTML
      pageLoadingIcon: '<div class="col-12 ajax-loader alt"><div class="spinner"></div></div>', // Page level loading icon
      nonce: '', 
      queryVars: {
        postsPerPage: 10, // Default number of posts per page
        page: 1,
      },
      form: null,
      secondaryForm: null,
    }, options);

    const $container = this;
    const $loadMoreButton = $(settings.loadMoreButton);

    // to prepare request data
    const prepareRequestData = ($frm, options = {}) => {      
      const formDataObject = $frm.serializeArray().reduce((acc, field) => {
        acc[field.name] = field.value;
        return acc;
      }, {});
      return $.extend(settings.queryVars, formDataObject, options);
    };


    // Function to load posts
    const loadPosts = (data) => {     
      // Make AJAX request
      $.post(settings.ajaxUrl, data, (response) => {
        // Remove page level ajax loader
        $container.find('.ajax-loader').remove();
        
        // Remove ajax loading icon
        $loadMoreButton.prop('disabled', false).find('.ajax-loading-icon').remove();
        
        if (response.success) {
          // Append new posts to the container
          $container.append(response.data.posts);

          // Check if there are more posts to load
          if (response.data.has_more_posts) {
            $loadMoreButton.show();
            settings.queryVars.page = response.data.next_page; // Update the page number dynamically               
            $(settings.loadMoreButton).attr('data-page', settings.queryVars.page); // Update data-page attribute
          } else {
            $loadMoreButton.hide();
          }
        } else {
          $loadMoreButton.hide();
          $container.append(response.data.message);
        }
      });
    }
    
    const reset = () => {
       // Reset page
      $(settings.loadMoreButton).attr('data-page', 1);

      // Reset the posts container to reload posts from the first page
      $container.html(''); // Clear existing posts
      $loadMoreButton.hide();
      
      // Show page-level loading icon while the first request is in progress
      $container.append(settings.pageLoadingIcon);
    }

    const handleRequest = () => {
      reset();
      const requestData = prepareRequestData($(settings.form), {
        page: 1
      });
      
      loadPosts(requestData);
    }    
    
    // Triggering the Load More action on the button click
    $(settings.loadMoreButton).on('click', function(e) {
      e.preventDefault();
      
      $(settings.loadMoreButton).prop('disabled', true).append(settings.loadingIcon);   
    
      let requestData = prepareRequestData($(settings.form), {
        page: $(settings.loadMoreButton).attr('data-page'),
        post_type: $(settings.loadMoreButton).data('type')
      });
      
      loadPosts(requestData);
    });

    // Attach event handlers for the dynamic form (settings.form)
    $(document).on('change', `${settings.form} select`, handleRequest); // Dynamic form selector
    $(document).on('keypress', `${settings.form} #search-filter`, function(e) {
      if (e.which === 13) {
        e.preventDefault();
        handleRequest();
      }
    });
   //$(document).on('ifChecked ifUnchecked', `${settings.secondaryForm} input[type="checkbox"]`, function(event) {
   $(document).on('change', `${settings.secondaryForm} input[type="checkbox"]`, function(event) {   
   
      let data = [];
      let checkedValues = [];
      const $form = $(settings.secondaryForm);
      const url = $form.attr('action');
      const frm_key = $form.attr('data-name');

      $form.find('input:checked').each(function() {
        const value = $(this).val();
        if (checkedValues.indexOf(value) === -1) { // Ensure no duplicates
          checkedValues.push(value);
        }
      });

      // Update the checked values array based on the event type
      const currentValue = $(this).val();
      if (event.type === 'ifChecked') {
        if (checkedValues.indexOf(currentValue) === -1) {
          checkedValues.push($(this).val());
        }
      } else if (event.type === 'ifUnchecked') {
        const valueIndex = checkedValues.indexOf(currentValue);
        if (valueIndex >= 0) {
          checkedValues.splice(valueIndex, 1); // Remove if present
        }
      }
      
      reset();      
      
      const requestData = prepareRequestData($(settings.form), {
        page: 1, 
        pub_category: checkedValues
      });
      
     loadPosts(requestData);
    });

    return this; // Maintain chainability
  };
})(jQuery);