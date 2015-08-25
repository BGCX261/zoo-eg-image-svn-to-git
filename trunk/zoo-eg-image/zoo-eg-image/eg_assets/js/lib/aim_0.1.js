var eg = eg||{};
eg.http = eg.http||{};

if (!eg.http.AIM){
	/**
	*
	*  AJAX IFRAME METHOD (AIM)
	*  http://www.webtoolkit.info/
	*  
	*  Зависимости:
	*  eg_js_0.1/common.js
	*  eg_js_0.1/util.js
        *  jQuery
	*  
	*  Как это работает:
	*  На html-странице есть форма загрузки файлов, 
	*  в которой, кроме обычных вещей для файловой формы, на событие onSubmit стоит AIM.submit().
	*  Также на этой странице есть две callback-функции, вызываемые по началу и
	*  завершению действий загрузки. Все это выглядит примерно таким образом:
	*  
	*  <form action="upload" method="post" enctype="multipart/form-data" 
	*  onsubmit="return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})">
	*  
	*  В данном случае на странице надо создать функции startCallback() и completeCallback(),
	*  в которых можно прописать произвольные действия перед отправкой запроса и после
	*  получения ответа сервера.
	*  
	*  После того, как юзер выбирает файл и нажимает кнопку, 
	*  создается невидимый iFrame с уникальным идентификатором,
	*  и прикрепляется к странице. К форме загрузки файла прицепляется 
	*  атрибут target, который указывает на созданный iframe.
	*  
	*  Происходит загрузка файлов, и ответ сервера появляется в спрятанном
	*  iFrame. Далее, функция completeCallback() может взять содержимое iFrame и отобразить его 
	*  пользователю в удобном виде.
	*  
	*  В процессе работы страница не перегружается, 
	*  только крутятся часики как напоминание о том, что идет запрос.
	*
	**/
	eg.http.AIM = {
			 
		/**Добавляет элемент iFrame к документу и прикрепяет callback-функцию обработки
		 * ответа сервера к этому iFrame.
		 */
		frame : function(c) {

			var n = 'f' + Math.floor(Math.random() * 99999);
			var d = document.createElement('DIV');
			d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="eg.http.AIM.loaded(\''+n+'\')"></iframe>';
			document.body.appendChild(d);

			var i = document.getElementById(n);
			if (c && typeof(c.onComplete) == 'function') {
				i.onComplete = c.onComplete;
			}

			return n;
		},
	  
		/**Прикрепляет атрибут target к форме, он указывает на созданный iFrame.*/
		form : function(f, name) {
			f.setAttribute('target', name);
		},

		/**Начинает работу. В зависимости от того, что возвращает callback-функция, 
		 * прикрепленная к onStart (true или false), произойдет загрузка файлов на сервер.
		 */
		submit : function(f, c) {
			eg.http.AIM.form(f, eg.http.AIM.frame(c));
			if (c && typeof(c.onStart) == 'function') {
				return c.onStart();
			} else {
				return true;
			}
		},

		/**Выполяет callback-функцию, срабатывающую после приема ответа сервера.*/
		loaded : function(id) {
			var i = document.getElementById(id);
			if (i.contentDocument) {
				var d = i.contentDocument;
			} else if (i.contentWindow) {
				var d = i.contentWindow.document;
			} else {
				var d = window.frames[id].document;
			}
			if (d.location.href == "about:blank") {
				return;
			}

			if (typeof(i.onComplete) == 'function') {
				var txt = $(d).find('body').html();
//				if (eg.util.BrowserDetect.browser == 'Explorer'){
//					txt = d.body.innerHTML;
//				}else{
//					txt = d.body.firstChild.innerHTML;
//				}
				i.onComplete(txt);
			}
		}
	}
}