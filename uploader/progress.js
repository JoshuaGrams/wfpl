function tag(name) {
    return document.getElementById(name);
}

var dbg_url;
function sendRequest(url,callback,postData) {
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
	req.setRequestHeader('User-Agent','XMLHTTP/1.0');
	if (postData)
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	dbg_url = url;
	req.onreadystatechange = function () {
		if(req.readyState != 4) {
			callback(req);
			return;
		}
		if (req.status != 200 && req.status != 304) {
			/* alert('url:' + dbg_url + '  HTTP error ' + req.status); */
			progress_start_delayed();
			return;
		}
		callback(req);
	}
	if (req.readyState == 4) return;
	req.send(postData);
}

var XMLHttpFactories = [
	function () {return new XMLHttpRequest()},
	function () {return new ActiveXObject("Msxml2.XMLHTTP")},
	function () {return new ActiveXObject("Msxml3.XMLHTTP")},
	function () {return new ActiveXObject("Microsoft.XMLHTTP")}
];

function createXMLHTTPObject() {
	var xmlhttp = false;
	for (var i=0;i<XMLHttpFactories.length;i++) {
		try {
			xmlhttp = XMLHttpFactories[i]();
		}
		catch (e) {
			continue;
		}
		break;
	}
	return xmlhttp;
}


function progress_start() {
	sendRequest('~url~', progress_update_with);
}

function progress_start_delayed() {
	setTimeout(progress_start, 1500);
}


function progress_finished() {
	var appears;
	tag('wfpl_progress_header').innerHTML = 'Upload Finished';
	appears = tag('wfpl_upload_finished');
	if(appears) {
		appears.style.position = 'static';
	}
	if(wfpl_upload_finished) {
		wfpl_upload_finished();
	}
}

function progress_update_with(rec) {
	length = rec.responseText.length;
	bar = tag('wfpl_progress_bar');
	bar.style.backgroundPosition = (Math.floor(length / 5) - 200) + 'px 0';

	whole = Math.floor(length/10);
	pct = '' + whole + '.' + (length - (whole * 10));
	bar.innerHTML = pct + '%';

	if(length == 1000) {
		progress_finished();
	}
}

function submitting() {
	if(wfpl_upload_starting) {
		wfpl_upload_starting();
	}
	tag('wfpl_progress_form').style.display = 'none';
	tag('wfpl_progress_section').style.position = 'static';
	progress_start_delayed();
}






