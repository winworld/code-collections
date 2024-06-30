var ddFn = {
    limitStrLength: function (str, maxLength = 6) {
        if (str.length > maxLength) {
            return str.slice(0, maxLength);
        }
        return str;
    },
    groupBy: function (xs, key) {
        return xs.reduce(function (rv, x) {
            (rv[x[key]] = rv[x[key]] || []).push(x);
            return rv;
        }, {});
    },
    populateDropdown: function (
      list,
      optionVal,
      eleId,
      removeFirstItem = false,
      selectedVal = null
    ) {
        if (!removeFirstItem) {
            $(eleId).find("option").not(":first").remove();
        }
        if (list.length > 0) {
            for (var i = 0; i < list.length; i++) {
                var option = $("<option>");
                for (var key in list[i]) {
                    if (key === optionVal) {
                        option.attr("value", list[i][key]);
                    } else {
                        option.html(list[i][key]);
                    }

                    if(selectedVal !== null && selectedVal == list[i][key]) {
                      option.attr('selected', 'selected');
                    }
                }
                $(eleId).append(option);
            }
        }
    },
  }
  $(document).ready(function () {
    window._token = $('meta[name="csrf-token"]').attr('content')

    moment.updateLocale('en', {
      week: {dow: 1} // Monday is the first day of the week
    })

    $('.date').datetimepicker({
      format: 'DD/MM/YYYY',
      locale: 'en',
      icons: {
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    })

    $('.datetime').datetimepicker({
      format: 'DD/MM/YYYY HH:mm:ss',
      locale: 'en',
      sideBySide: true,
      icons: {
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    })

    $('.timepicker').datetimepicker({
      format: 'HH:mm:ss',
      icons: {
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    })

    $('.select-all').click(function () {
      let $select2 = $(this).parent().siblings('.select2')
      $select2.find('option').prop('selected', 'selected')
      $select2.trigger('change')
    })
    $('.deselect-all').click(function () {
      let $select2 = $(this).parent().siblings('.select2')
      $select2.find('option').prop('selected', '')
      $select2.trigger('change')
    })

    $('.select2').select2()

    $('.treeview').each(function () {
      var shouldExpand = false
      $(this).find('li').each(function () {
        if ($(this).hasClass('active')) {
          shouldExpand = true
        }
      })
      if (shouldExpand) {
        $(this).addClass('active')
      }
    })

    $('.c-header-toggler.mfs-3.d-md-down-none').click(function (e) {
      $('#sidebar').toggleClass('c-sidebar-lg-show');

      setTimeout(function () {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      }, 400);
    });

  })
