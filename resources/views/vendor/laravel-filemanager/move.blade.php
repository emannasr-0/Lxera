<ul class=" js-font-resize nav nav-pills flex-column">
  @foreach($root_folders as $root_folder)
    <li class=" js-font-resize nav-item">
      <a class=" js-font-resize nav-link" href="#" data-type="0" onclick="moveToNewFolder(`{{$root_folder->url}}`)">
        <i class=" js-font-resize fa fa-folder fa-fw"></i> {{ $root_folder->name }}
        <input type="hidden" id="goToFolder" name="goToFolder" value="{{ $root_folder->url }}">
        <div id="items">
          @foreach($items as $i)
            <input type="hidden" id="{{ $i }}" name="items[]" value="{{ $i }}">
          @endforeach
        </div>
      </a>
    </li>
    @foreach($root_folder->children as $directory)
    <li class=" js-font-resize nav-item sub-item">
      <a class=" js-font-resize nav-link" href="#" data-type="0" onclick="moveToNewFolder(`{{$directory->url}}`)">
        <i class=" js-font-resize fa fa-folder fa-fw"></i> {{ $directory->name }}
        <input type="hidden" id="goToFolder" name="goToFolder" value="{{ $directory->url }}">
        <div id="items">
          @foreach($items as $i)
            <input type="hidden" id="{{ $i }}" name="items[]" value="{{ $i }}">
          @endforeach
        </div>
      </a>
    </li>
    @endforeach
  @endforeach
</ul>

<script>
  function moveToNewFolder($folder) {
    $("#notify").modal('hide');
    var items =[];
    $("#items").find("input").each(function() {items.push(this.id)});
    performLfmRequest('domove', {
      items: items,
      goToFolder: $folder
    }).done(refreshFoldersAndItems);
  }
</script>
