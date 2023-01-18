@can('user_meta_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.user-meta.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.userMeta.title_singular') }}
            </a>
        </div>
    </div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('cruds.userMeta.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-userUserMeta">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.userMeta.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.userMeta.fields.user') }}
                        </th>
                        <th>
                            {{ trans('cruds.userMeta.fields.meta_key') }}
                        </th>
                        <th>
                            {{ trans('cruds.userMeta.fields.meta_value') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userMeta as $key => $userMeta)
                        <tr data-entry-id="{{ $userMeta->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $userMeta->id ?? '' }}
                            </td>
                            <td>
                                {{ $userMeta->user->name ?? '' }}
                            </td>
                            <td>
                                {{ $userMeta->meta_key ?? '' }}
                            </td>
                            <td>
                                {{ $userMeta->meta_value ?? '' }}
                            </td>
                            <td>
                                @can('user_meta_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.user-meta.show', $userMeta->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('user_meta_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.user-meta.edit', $userMeta->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('user_meta_delete')
                                    <form action="{{ route('admin.user-meta.destroy', $userMeta->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('user_meta_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.user-meta.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 10,
  });
  let table = $('.datatable-userUserMeta:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection