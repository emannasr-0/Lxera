<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Chrome, Firefox OS and Opera -->
  <meta name="theme-color" content="#333844">
  <!-- Windows Phone -->
  <meta name="msapplication-navbutton-color" content="#333844">
  <!-- iOS Safari -->
  <meta name="apple-mobile-web-app-status-bar-style" content="#333844">

  <title>{{ trans('laravel-filemanager::lfm.title-page') }}</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('vendor/laravel-filemanager/img/72px color.png') }}">
  <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="/assets/default/vendors/jquery-ui/jquery-ui.min.css">
  <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/cropper.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/dropzone.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/mime-icons.min.css') }}">
  <style>{!! \File::get(base_path('vendor/unisharp/laravel-filemanager/public/css/lfm.css')) !!}</style>
  {{-- Use the line below instead of the above if you need to cache the css. --}}
  {{-- <link rel="stylesheet" href="{{ asset('/vendor/laravel-filemanager/css/lfm.css') }}"> --}}
</head>
<body>
  <nav class=" js-font-resize navbar sticky-top navbar-expand-lg navbar-dark" id="nav">
    <a class=" js-font-resize navbar-brand invisible-lg d-none d-lg-inline" id="to-previous">
      <i class=" js-font-resize fas fa-arrow-left fa-fw"></i>
      <span class=" js-font-resize d-none d-lg-inline">{{ trans('laravel-filemanager::lfm.nav-back') }}</span>
    </a>
    <a class=" js-font-resize navbar-brand d-block d-lg-none" id="show_tree">
      <i class=" js-font-resize fas fa-bars fa-fw"></i>
    </a>
    <a class=" js-font-resize navbar-brand d-block d-lg-none" id="current_folder"></a>
    <a id="loading" class=" js-font-resize navbar-brand"><i class=" js-font-resize fas fa-spinner fa-spin"></i></a>
    <div class=" js-font-resize ml-auto px-2">
      <a class=" js-font-resize navbar-link d-none" id="multi_selection_toggle">
        <i class=" js-font-resize fa fa-check-double fa-fw"></i>
        <span class=" js-font-resize d-none d-lg-inline">{{ trans('laravel-filemanager::lfm.menu-multiple') }}</span>
      </a>
    </div>
    <a class=" js-font-resize navbar-toggler collapsed border-0 px-1 py-2 m-0" data-toggle="collapse" data-target="#nav-buttons">
      <i class=" js-font-resize fas fa-cog fa-fw"></i>
    </a>
    <div class=" js-font-resize collapse navbar-collapse flex-grow-0" id="nav-buttons">
      <ul class=" js-font-resize navbar-nav">
        <li class=" js-font-resize nav-item">
          <a class=" js-font-resize nav-link" data-display="grid">
            <i class=" js-font-resize fas fa-th-large fa-fw"></i>
            <span>{{ trans('laravel-filemanager::lfm.nav-thumbnails') }}</span>
          </a>
        </li>
        <li class=" js-font-resize nav-item">
          <a class=" js-font-resize nav-link" data-display="list">
            <i class=" js-font-resize fas fa-list-ul fa-fw"></i>
            <span>{{ trans('laravel-filemanager::lfm.nav-list') }}</span>
          </a>
        </li>
        <li class=" js-font-resize nav-item dropdown">
          <a class=" js-font-resize nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <i class=" js-font-resize fas fa-sort fa-fw"></i>{{ trans('laravel-filemanager::lfm.nav-sort') }}
          </a>
          <div class=" js-font-resize dropdown-menu dropdown-menu-right border-0"></div>
        </li>
      </ul>
    </div>
  </nav>

  <nav class=" js-font-resize bg-light fixed-bottom border-top d-none" id="actions">
    <a data-action="open" data-multiple="false"><i class=" js-font-resize fas fa-folder-open"></i>{{ trans('laravel-filemanager::lfm.btn-open') }}</a>
    <a data-action="preview" data-multiple="true"><i class=" js-font-resize fas fa-images"></i>{{ trans('laravel-filemanager::lfm.menu-view') }}</a>
    <a data-action="use" data-multiple="true"><i class=" js-font-resize fas fa-check"></i>{{ trans('laravel-filemanager::lfm.btn-confirm') }}</a>
  </nav>

  <div class=" js-font-resize d-flex flex-row">
    <div id="tree"></div>

    <div id="main">
      <div id="alerts"></div>

      <nav aria-label="breadcrumb" class=" js-font-resize d-none d-lg-block" id="breadcrumbs">
        <ol class=" js-font-resize breadcrumb">
          <li class=" js-font-resize breadcrumb-item invisible">Home</li>
        </ol>
      </nav>

      <div id="empty" class=" js-font-resize d-none">
        <i class=" js-font-resize far fa-folder-open"></i>
        {{ trans('laravel-filemanager::lfm.message-empty') }}
      </div>

      <div id="content"></div>
      <div id="pagination"></div>

      <a id="item-template" class=" js-font-resize d-none">
        <div class=" js-font-resize square"></div>

        <div class=" js-font-resize info">
          <div class=" js-font-resize item_name text-truncate"></div>
          <time class=" js-font-resize text-muted font-weight-light text-truncate"></time>
        </div>
      </a>
    </div>

    <div id="fab"></div>
  </div>

  <div class=" js-font-resize modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class=" js-font-resize modal-dialog">
      <div class=" js-font-resize modal-content">
        <div class=" js-font-resize modal-header">
          <h4 class=" js-font-resize modal-title" id="myModalLabel">{{ trans('laravel-filemanager::lfm.title-upload') }}</h4>
          <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close"><span aia-hidden="true">&times;</span></button>
        </div>
        <div class=" js-font-resize modal-body">
          <form action="{{ route('unisharp.lfm.upload') }}" role='form' id='uploadForm' name='uploadForm' method='post' enctype='multipart/form-data' class=" js-font-resize dropzone">
            <div class=" js-font-resize form-group" id="attachment">
              <div class=" js-font-resize controls text-center">
                <div class=" js-font-resize input-group w-100">
                  <a class=" js-font-resize btn btn-primary w-100 text-white" id="upload-button">{{ trans('laravel-filemanager::lfm.message-choose') }}</a>
                </div>
              </div>
            </div>
            <input type='hidden' name='working_dir' id='working_dir'>
            <input type='hidden' name='type' id='type' value='{{ request("type") }}'>
            <input type='hidden' name='_token' value='{{csrf_token()}}'>
          </form>
        </div>
        <div class=" js-font-resize modal-footer">
          <button type="button" class=" js-font-resize btn btn-secondary w-100" data-dismiss="modal">{{ trans('laravel-filemanager::lfm.btn-close') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div class=" js-font-resize modal fade" id="notify" tabindex="-1" role="dialog" aria-hidden="true">
    <div class=" js-font-resize modal-dialog modal-lg">
      <div class=" js-font-resize modal-content">
        <div class=" js-font-resize modal-body"></div>
        <div class=" js-font-resize modal-footer">
          <button type="button" class=" js-font-resize btn btn-secondary w-100" data-dismiss="modal">{{ trans('laravel-filemanager::lfm.btn-close') }}</button>
          <button type="button" class=" js-font-resize btn btn-primary w-100" data-dismiss="modal">{{ trans('laravel-filemanager::lfm.btn-confirm') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div class=" js-font-resize modal fade" id="dialog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class=" js-font-resize modal-dialog modal-lg">
      <div class=" js-font-resize modal-content">
        <div class=" js-font-resize modal-header">
          <h4 class=" js-font-resize modal-title"></h4>
        </div>
        <div class=" js-font-resize modal-body">
          <input type="text" class=" js-font-resize form-control">
        </div>
        <div class=" js-font-resize modal-footer">
          <button type="button" class=" js-font-resize btn btn-secondary w-100" data-dismiss="modal">{{ trans('laravel-filemanager::lfm.btn-close') }}</button>
          <button type="button" class=" js-font-resize btn btn-primary w-100" data-dismiss="modal">{{ trans('laravel-filemanager::lfm.btn-confirm') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div id="carouselTemplate" class=" js-font-resize d-none carousel slide bg-light" data-ride="carousel">
    <ol class=" js-font-resize carousel-indicators">
      <li data-target="#previewCarousel" data-slide-to="0" class=" js-font-resize active"></li>
    </ol>
    <div class=" js-font-resize carousel-inner">
      <div class=" js-font-resize carousel-item active">
        <a class=" js-font-resize carousel-label"></a>
        <div class=" js-font-resize carousel-image"></div>
      </div>
    </div>
    <a class=" js-font-resize carousel-control-prev" href="#previewCarousel" role="button" data-slide="prev">
      <div class=" js-font-resize carousel-control-background" aria-hidden="true">
        <i class=" js-font-resize fas fa-chevron-left"></i>
      </div>
      <span class=" js-font-resize sr-only">Previous</span>
    </a>
    <a class=" js-font-resize carousel-control-next" href="#previewCarousel" role="button" data-slide="next">
      <div class=" js-font-resize carousel-control-background" aria-hidden="true">
        <i class=" js-font-resize fas fa-chevron-right"></i>
      </div>
      <span class=" js-font-resize sr-only">Next</span>
    </a>
  </div>

  <script src="/assets/admin/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="/assets/admin/vendor/poper/popper.min.js"></script>
  <script src="/assets/admin/vendor/bootstrap/bootstrap.min.js"></script>
  <script src="/assets/default/vendors/jquery-ui/jquery-ui.min.js"></script>
  <script src="{{ asset('vendor/laravel-filemanager/js/cropper.min.js') }}"></script>
  <script src="{{ asset('vendor/laravel-filemanager/js/dropzone.min.js') }}"></script>
  <script>
    var lang = {!! json_encode(trans('laravel-filemanager::lfm')) !!};
    var actions = [
      // {
      //   name: 'use',
      //   icon: 'check',
      //   label: 'Confirm',
      //   multiple: true
      // },
      {
        name: 'rename',
        icon: 'edit',
        label: lang['menu-rename'],
        multiple: false
      },
      {
        name: 'download',
        icon: 'download',
        label: lang['menu-download'],
        multiple: true
      },
      // {
      //   name: 'preview',
      //   icon: 'image',
      //   label: lang['menu-view'],
      //   multiple: true
      // },
      {
        name: 'move',
        icon: 'paste',
        label: lang['menu-move'],
        multiple: true
      },
      {
        name: 'resize',
        icon: 'arrows-alt',
        label: lang['menu-resize'],
        multiple: false
      },
      {
        name: 'crop',
        icon: 'crop',
        label: lang['menu-crop'],
        multiple: false
      },
      {
        name: 'trash',
        icon: 'trash',
        label: lang['menu-delete'],
        multiple: true
      },
    ];

    var sortings = [
      {
        by: 'alphabetic',
        icon: 'sort-alpha-down',
        label: lang['nav-sort-alphabetic']
      },
      {
        by: 'time',
        icon: 'sort-numeric-down',
        label: lang['nav-sort-time']
      }
    ];
  </script>
  <script>{!! \File::get(base_path('vendor/unisharp/laravel-filemanager/public/js/script.js')) !!}</script>
  {{-- Use the line below instead of the above if you need to cache the script. --}}
  {{-- <script src="{{ asset('vendor/laravel-filemanager/js/script.js') }}"></script> --}}
  <script>
    Dropzone.options.uploadForm = {
      paramName: "upload[]", // The name that will be used to transfer the file
      uploadMultiple: false,
      parallelUploads: 5,
      timeout:0,
      clickable: '#upload-button',
      dictDefaultMessage: lang['message-drop'],
      init: function() {
        var _this = this; // For the closure
        this.on('success', function(file, response) {
          if (response == 'OK') {
            loadFolders();
          } else {
            this.defaultOptions.error(file, response.join('\n'));
          }
        });
      },
      headers: {
        'Authorization': 'Bearer ' + getUrlParam('token')
      },
      acceptedFiles: "{{ implode(',', $helper->availableMimeTypes()) }}",
      maxFilesize: ({{ $helper->maxUploadSize() }} / 1000)
    }
  </script>
</body>
</html>
