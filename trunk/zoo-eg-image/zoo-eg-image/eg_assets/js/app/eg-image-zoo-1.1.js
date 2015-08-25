var eg = eg||{};
eg.image = eg.image||{};
eg.upload = {
    common: {}
};

eg.upload.common.speed = 'medium';

eg.upload.common.setStatus = function(txt){
    $('#eg-upl-form-status').text(txt);
}

eg.upload.common.setDescr = function(txt){
    $('#eg-upl-form-descr').text(txt);
}

eg.upload.common.uploadStart = function(){
    if (this.uploadForm.find(':file').val()){
        this.setStatus('Загрузка...');
        this.uploadForm.find(':submit').attr('disabled', true);
        return true;
    }
    return false;
}

eg.upload.common.uploadComplete = function(respText, uplComplete){
    this.setStatus('Загрузка завершена');
    this.uploadForm.find(':submit').removeAttr('disabled');
    var res;
    try{
        res = eval("(" + respText + ")");
    }catch(e){
        alert(respText);
    }
    if (res.res){
        uplComplete(res);
    }else{
        alert("Ошибка:\n"+res.error);
    }
    this.uploadContainer.hide(this.speed);
}

eg.upload.common.showPopupForm = function(formActionURL, startCallback, completeCallback){
    if (!this.uploadContainer){
        this.uploadContainer = $('<div/>').attr('id', 'eg-modal-upload-form-container');
        $('body').append(this.uploadContainer);
        this.uploadForm = $('<form/>').attr({
            method: 'POST',
            enctype: 'multipart/form-data',
            encoding: 'multipart/form-data'
        });

        this.uploadForm.append($('<span id="eg-upl-form-descr"></span><br/>'));
        this.uploadForm.append($('<input name="upfile" type="file">'));
        this.uploadForm.append($('<input type="submit" value="Отправить">'));
        this.uploadForm.append($('<br/><span id="eg-upl-form-status"></span>'));

        this.uploadContainer.append(this.uploadForm);

        var this_ = this;
        this.uploadContainer.append($('<div/>').addClass('eg-close')
            .append($('<span>[x]<span>').click(function(){
                this_.uploadContainer.hide(this_.speed);
            })));
    }else{
        this.uploadContainer.hide();
    }

    this.uploadForm.unbind();
    this.uploadForm.submit(function(){
        return eg.http.AIM.submit(this, {
            onStart : startCallback,
            onComplete : completeCallback
        });
    });

    this.uploadForm.attr('action', formActionURL);
    this.uploadForm.find(':file').val('');
    this.uploadContainer.show(this.speed);
}

eg.image.upload = function(button, opt){
    eg.upload.common.showPopupForm(opt.uploadURL, function(){
        return eg.upload.common.uploadStart();
    }, function(respText){
        eg.upload.common.uploadComplete(respText, function(res){
            eg.image.uploadComplete(res, button);
        });
    });
    eg.upload.common.setStatus('');
    eg.upload.common.setDescr('Выберите изображение для загрузки')
};
    
eg.image.uploadComplete = function(res, button){
    button = $(button);
    var imgHolder = button.parents('.eg-image-container:first')
    .find('.eg-item-image-holder').get(0);
    imgHolder = $(imgHolder);
    imgHolder.empty();
    imgHolder.append($('<img/>').attr({
        src: res.img,
        alt: 'image'
    }));
}
    
