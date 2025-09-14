{{ Form::open(['route' => ['productlocation.update', $productLocation->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('name', __('Location Name'), ['class' => 'form-label']) }}
        {{ Form::text('name', $productLocation->name, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group">
        {{ Form::label('type', __('Location Type'), ['class' => 'form-label']) }}<x-required></x-required>
        <select name="type" id="type" class="form-control cattype" data-toggle="select2" required>
            <option value="" disabled>{{ __('Select Type') }}</option>
            @foreach ($types as $key => $type)
            <option value="{{ $key }}" {{ $productLocation->type == $key ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
    {{ Form::close() }}
</div>


<!-- 
<script>
    $(document).on('click', '[data-ajax-popup="true"]', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var title = $(this).data('title');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                // Append a modal if not exists
                if ($('#ajaxModal').length === 0) {
                    $('body').append('<div class="modal fade" id="ajaxModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
                }
                $('#ajaxModal .modal-content').html(data);
                $('#ajaxModal').modal('show');
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Something went wrong!');
            }
        });
    });
</script> -->