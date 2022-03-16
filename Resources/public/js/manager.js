$(function () {

    var $renameModal = $('#js-confirm-rename');
    var $deleteModal = $('#js-confirm-delete');
    var $displayModal = $('#js-display-image');
    var callback = function (key, opt) {
        switch (key) {
            case 'edit':
                var $renameModalButton = opt.$trigger.find(".js-rename-modal")
                renameFile($renameModalButton)
                $renameModal.modal("show");
                break;
            case 'delete':
                var $deleteModalButton = opt.$trigger.find(".js-delete-modal")
                deleteFile($deleteModalButton)
                $deleteModal.modal("show");
                break;
            case 'download':
                var $downloadButton = opt.$trigger.find(".js-download")
                downloadFile($downloadButton)
                break;
            case 'preview':
                var $previewModalButton = opt.$trigger.find(".js-open-modal")
                previewFile($previewModalButton)
                $displayModal.modal("show");
                break;
        }
    };

    $.contextMenu({
        selector: '.file',
        callback: callback,
        items: {
            "delete": {name: deleteMessage, icon: "far fa-trash-alt"},
            "edit": {name: renameMessage, icon: "far fa-edit"},
            "download": {name: downloadMessage, icon: "fas fa-download"},
        }
    });
    $.contextMenu({
        selector: '.img',
        callback: callback,
        items: {
            "delete": {name: deleteMessage, icon: "far fa-trash-alt"},
            "edit": {name: renameMessage, icon: "far fa-edit"},
            "download": {name: downloadMessage, icon: "fas fa-download"},
            "preview": {name: previewMessage, icon: "fas fa-eye"},
        }
    });
    $.contextMenu({
        selector: '.dir',
        callback: callback,
        items: {
            "delete": {name: deleteMessage, icon: "far fa-trash-alt"},
            "edit": {name: renameMessage, icon: "far fa-edit"},
        }
    });

    function renameFile($renameModalButton) {
        $('#form_name').val($renameModalButton.data('name'));
        $('#form_extension').val($renameModalButton.data('extension'));
        $renameModal.find('form').attr('action', $renameModalButton.data('href'))
    }

    function deleteFile($deleteModalButton) {
        $('#js-confirm-delete').find('form').attr('action', $deleteModalButton.data('href'));
    }

    function previewFile($previewModalButton) {
        var href = addParameterToURL($previewModalButton.data('href'), 'time=' + new Date().getTime());
        $('#js-display-image').find('img').attr('src', href);
    }

    function addParameterToURL(_url, param) {
        _url += (_url.split('?')[1] ? '&' : '?') + param;
        return _url;
    }

    function downloadFile($downloadButton) {
        $downloadButton[0].click();
    }

    function initTree(treedata) {
        $('#tree').jstree({
            'core': {
                'data': treedata,
                "check_callback": true
            }
        }).bind("changed.jstree", function (e, data) {
            if (data.node) {
                document.location = data.node.a_attr.href;
            }
        });
    }

    if (tree === true) {

        // sticky kit
        $("#tree-block").stick_in_parent();

        initTree(treedata);
    }
    $(document)
    // checkbox select all
        .on('click', '#select-all', function () {
            var checkboxes = $('#form-multiple-delete').find(':checkbox')
            if ($(this).is(':checked')) {
                checkboxes.prop('checked', true);
            } else {
                checkboxes.prop('checked', false);
            }
        })
        // delete modal buttons
        .on('click', '.js-delete-modal', function () {
                deleteFile($(this));
            }
        )
        // preview modal buttons
        .on('click', '.js-open-modal', function () {
                previewFile($(this));
            }
        )
        // rename modal buttons
        .on('click', '.js-rename-modal', function () {
                renameFile($(this));
            }
        )
        // multiple delete modal button
        .on('click', '#js-delete-multiple-modal', function () {
            var $multipleDelete = $('#form-multiple-delete').serialize();
            if ($multipleDelete) {
                var href = urldelete + '&' + $multipleDelete;
                $('#js-confirm-delete').find('form').attr('action', href);
            }
        })
        // checkbox
        .on('click', '#form-multiple-delete :checkbox', function () {
            var $jsDeleteMultipleModal = $('#js-delete-multiple-modal');
            if ($(".checkbox").is(':checked')) {
                $jsDeleteMultipleModal.removeClass('disabled');
            } else {
                $jsDeleteMultipleModal.addClass('disabled');
            }
        });

    // preselected
    $renameModal.on('shown.bs.modal', function () {
        $('#form_name').select().mouseup(function () {
            $('#form_name').unbind("mouseup");
            return false;
        });
    });
    $('#addFolder').on('shown.bs.modal', function () {
        $('#rename_name').select().mouseup(function () {
            $('#rename_name').unbind("mouseup");
            return false;
        });
    });


    // Module Tiny
    if (moduleName === 'tiny') {

        $('#form-multiple-delete').on('click', '.select', function () {


            var windowManager = top != undefined && top.tinymceWindowManager != undefined ? top.tinymceWindowManager : '';

            // tinymce 5
            if (windowManager != '') {
                if (top.tinymceCallBackURL != undefined)
                    top.tinymceCallBackURL = $(this).attr("data-path");
                windowManager.close();
            } else {
                // tinymce 4
                var args = top.tinymce.activeEditor.windowManager.getParams();
                var input = args.input;
                var document = args.window.document;
                var divInputSplit = document.getElementById(input).parentNode.id.split("_");

                // set url
                document.getElementById(input).value = $(this).attr("data-path");

                // set width and height
                var baseId = divInputSplit[0] + '_';
                var baseInt = parseInt(divInputSplit[1], 10);

                divWidth = baseId + (baseInt + 3);
                divHeight = baseId + (baseInt + 5);

                document.getElementById(divWidth).value = $(this).attr("data-width");
                document.getElementById(divHeight).value = $(this).attr("data-height");

                top.tinymce.activeEditor.windowManager.close();
            }

        });
    }

    // Module CKEditor
    if (moduleName === 'ckeditor') {
        $('#form-multiple-delete').on('click', '.select', function () {
            var url = new URL(window.location.href);
            var funcNum = url.searchParams.get("CKEditorFuncNum");
            var fileUrl = $(this).attr("data-path");
            window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
            window.close();
        });
    }

    // Global functions
    // display error alert
    function displayError(msg) {
        displayAlert('danger', msg)
    }

    // display success alert
    function displaySuccess(msg) {
        displayAlert('success', msg)
    }

    // file upload
    $("#fileupload").on("click", function(e) {
        // e.preventDefault();
            if($('#media_name').val() == '' || $('#media_name').val() == null) { // zero-length string
                // e.preventDefault();
                $('#addFileMessage').removeClass('alert alert-success');
                $('#addFileMessage').removeClass('alert alert-warning');
                $('#addFileMessage').addClass('alert alert-danger');
                $('#addFileMessage').html('نام نمیتواند خالی باشد');
                // alert('if');
            } else {
                // alert('else');
                $('#addFileMessage').removeClass('alert alert-success');
                $('#addFileMessage').removeClass('alert alert-danger');
                $('#addFileMessage').addClass('alert alert-warning');
                $('#addFileMessage').html('در حال آپلود فایل');
                $('#fileupload').fileupload({
                    dataType: 'json',
                    processQueue: false,
                    dropZone: $('#dropzone')
                }).on('fileuploaddone', function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        // alert('this is alert 1');
                        if (file.url) {
                            displaySuccess('<strong>' + file.name + '</strong> ' + successMessage)
                            // Ajax update view
                            // alert('this is alert 2');
                            $.ajax({
                                dataType: "json",
                                url: url,
                                type: 'GET'
                            }).done(function (data) {
                                // update file list
                                $('#form-multiple-delete').html(data.data);

                                lazy();

                                if (tree === true) {
                                    $('#tree').data('jstree', false).empty();
                                    initTree(data.treeData);
                                }

                                $('#select-all').prop('checked', false);
                                $('#js-delete-multiple-modal').addClass('disabled');

                            }).fail(function (jqXHR, textStatus, errorThrown) {
                                displayError('<strong>Ajax call error :</strong> ' + jqXHR.status + ' ' + errorThrown)
                            });

                        } else if (file.error) {
                            displayError('<strong>' + file.name + '</strong> ' + file.error)
                        }
                    });
                    $('#addFileMessage').removeClass('alert alert-warning');
                    $('#addFileMessage').html('');
                }).on('fileuploadfail', function (e, data) {
                    $.each(data.files, function (index, file) {
                        $('#addFileMessage').removeClass('alert alert-warning');
                        $('#addFileMessage').html('');
                        // console.log(data);
                        displayError('File upload failed.')
                    });
                });
            }
        });

    $("#remoteFileupload").on("click", function(e){
        e.preventDefault();
        // var form = $('media_remote');
        // get the properties and values from the form
        // var form_data = form.serialize();
        var form_data = {
            name: $('#media_remote_name').val(),
            tag: $('#media_remote_tag').val(),
            url: $('#media_remote_url').val(),
            token: $('#media_remote__token').val(),
        };
        console.log(form_data);

        // always makes sense to signal user that something is happening
        // $('#loadingSpinner').show();

        // simple approach avoid submitting multiple times
        $('#remoteFileupload').attr("disabled",true);
        $('#addRemoteFileMessage').removeClass('alert alert-success');
        $('#addRemoteFileMessage').removeClass('alert alert-danger');
        $('#addRemoteFileMessage').addClass('alert alert-warning');
        $('#addRemoteFileMessage').html('در حال آپلود فایل');

        // the actual request to your newAction
        $.ajax({
            url: urlremote,
            type: 'POST',
            dataType: 'json',
            data: form_data,
            success:function(data){
                if(data['status'] == 'success') {
                    console.log('success');
                    console.log(data['message']);
                    // handling the response data from the controller
                    // if(data.status == 'error'){
                    //     console.log("[API] ERROR: "+data.message);
                    // }
                    // if(data.status == 'success'){
                    //     console.log("[API] SUCCESS: "+data.message);
                    // }
                    $('#addRemoteFileMessage').removeClass('alert alert-warning');
                    $('#addRemoteFileMessage').addClass('alert alert-success');
                    $('#addRemoteFileMessage').html(data['message']);
                    setTimeout(function() {$('#addRemoteFile').modal('hide');$('#addRemoteFileMessage').removeClass('alert alert-success');}, 4000);
                    // $('#addRemoteFile').modal('hide');
                    location.reload();

                    // signal to user the action is done
                    // $('#loadingSpinner').hide();
                    $('#remoteFileupload').attr("disabled", false);
                }
                if(data['status'] == 'error') {
                    console.log('error');
                    $('#addRemoteFileMessage').removeClass('alert alert-warning');
                    $('#addRemoteFileMessage').addClass('alert alert-danger');
                    $('#addRemoteFileMessage').html(data['message']);
                    // $('#addRemoteFileMessage').removeClass('col-xs-5 col-md-4 col-lg-3 alert alert-danger');
                    $('#remoteFileupload').attr("disabled", false);
                }
            }
        });
    });


    function lazy() {
        $('.lazy').Lazy({});
    }

    lazy();
});