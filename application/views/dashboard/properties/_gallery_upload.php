<div class="row">
    <div class="col-md-12">
        <label for="username"><?= get_lang("Gallery") ?></label>
        <div class="form-group col-lg-12 col-md-12" style="margin-top:10px;padding-bottom:10px;">
            <!--Adding excess tenant images by PM in work order -->
            <div id="thumb-output" style="float:left;margin-left:10px;"></div>
            <label class="pm-file-container text-center" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= get_lang("Add more photos") ?>">
                <i class="fa fa-picture-o"></i>
                <input type="file" id="file-input" multiple="">
            </label>
            <!--Adding excess tenant images by PM in work order -->
        </div>            
        <!-- <a href="javascript:void(0);" onclick="javascript: uploadImages(57);" style="display:inline-block;margin:5px;">Upload</a> -->
    </div>
</div>
<link rel="stylesheet" type="text/css" href="assets/css/local.altStyle.css" />

<script type="text/javascript">

    /**
     * 
     */
    function uploadImages(propertyId)
    {
        if ($("#thumb-output").find(".thumb1").length > 0) {
            var gallery_count = $('.thumb').length;
            var done_count = 0;
            add_progress_msg('Uploading images for property (#' + propertyId + ')...');
            $('.thumb').each(function (i, obj) {

                var images = $(this).attr('src');
                var indexOf = images.indexOf("base64,");
                var imgSource = images.substring(indexOf + 7, images.length);

                var promise = $.ajax({
                    url: 'Gallery/Upload',
                    data: { imgSource: imgSource, propertyId: propertyId },
                    type: 'POST'
                });

                promise.fail((xhr, status, error) => {
                    var msg = "Gallery/Upload failed: status=" + status + ",error=" + error + ", " + xhr.responseText;
                    console.log(msg);
                    console.log(xhr);
                    add_progress_msg(msg);
                });

                promise.done((result) => {                    
                    console.log("Gallery/Upload done: " + result);
                    add_progress_msg(parseInt(done_count+1) + ' uploaded successfully.');
                });
                
                promise.always((result) => {
                    console.log("Gallery/Upload always: " + result);
                    done_count++;
                    if (done_count == gallery_count)
                    {
                        add_progress_msg('All images have been uploaded successfully.');
                        add_progress_msg('Congratulations! Your property has been saved. <a href="Property/Detail/' + propertyId + '">Hava a look</a>');
                    }
                });
            });
        }
        else
        {
            add_progress_msg('Congratulations! Your property has been saved. <a href="Property/Detail/' + propertyId + '">Hava a look</a>');
        }
    }

    $(document).ready(function () {
        // 1. register events 
        registerEvents();

        // 2. load existing images
        if (gallery_json != undefined && gallery_json != null && gallery_json != "") 
        {
            var images = JSON.parse(gallery_json);
            for (var i = 0; i < images.length; i++)
            {
                var img = images[i];
                loadImage(img, true);
            }
        }
    });

    /**
     * 
     */
    function loadImage(imageSrc, uploaded)
    {
        uploaded = uploaded || false;
        var img = $('<img></optionList>').addClass('thumb').attr('src', imageSrc); //create image element
        var imgDiv = (uploaded == false) ? '<div class="dv-thumb thumb1"></div>' : '<div class="dv-thumb thumb2"></div>';
        var anchor = $(imgDiv).append(img);
        $('#thumb-output').append(anchor); //append image to output element
    }

    /**
     * 
     */
    function registerEvents()
    {
        // 
        $('#file-input').on('change', function () { //on file input change
            if (window.File && window.FileReader && window.FileList && window.Blob) //check File API supported browser
            {
                var data = $(this)[0].files; //this file data

                $.each(data, function (index, file) { //loop though each file
                    if (/(\.|\/)(gif|jpe?g|png)$/i.test(file.type)) { //check supported file type
                        var fRead = new FileReader(); //new filereader
                        fRead.onload = (function (file) { //trigger function on successful read
                            return function (e) {
                                // var img = $('<img></optionList>').addClass('thumb').attr('src', e.target.result); //create image element
                                // var anchor = $('<div class="thumb1"></div>').append(img);
                                // $('#thumb-output').append(anchor); //append image to output element
                                loadImage(e.target.result);
                            };
                        })(file);
                        fRead.readAsDataURL(file); //URL representing the file's data.
                    }
                });
            } else {
                alert("Your browser doesn't support File API!"); //if File API is absent
            }
        });

        //To remove an image if clicked on remove image - deletes the image clicked from uploaded items.
        $('#thumb-output').on('click', '.dv-thumb', function () {
            $(this).remove();
            if ($(this).hasClass("thumb2"))
            {
                var delete_url = BASEURL + "Gallery/Delete";
                var propertyid = $('#propertyid').val();
                var imageUrl = $(this).find('img').attr('src');
                
                var promise = $.post(delete_url, {
                    propertyId: propertyid,
                    imgSource: imageUrl
                });

                promise.fail((xhr, status, error)=>{
                    console.log("fail: " + xhr.responseText);
                });

                promise.done((data)=>{
                    console.log("done: " + data);
                });
            }
        });  
    }

</script>