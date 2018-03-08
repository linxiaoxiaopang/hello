var listTable = new Object;

listTable.filter = new Object;
listTable.url = "";

/**
 * 切换排序方式
 */
listTable.sort = function (sort_by, sort_order) {
	var args = "sort_by=" + sort_by + "&sort_order=";
	if (sort_order)
		args += sort_order;
	else {
		if (this.filter.sort_by == sort_by) {
			args += this.filter.sort_order == "DESC" ? "ASC" : "DESC";
		} else {
			args += "DESC";
		}
		
	}
	
	args += this.compileFilter();
	
	var _this = this;
	$.ajax({
		type : "POST",
		url : _this.url,
		data : args,
		dataType : "json",
		success : function (json) {
			_this.listCallback(json);
		}
	});
}

/**
 * 翻页
 */
listTable.gotoPage = function (page) {
	if (page != null)
		this.filter['page'] = page;
	
	if (this.filter['page'] > this.pageCount)
		this.filter['page'] = 1;
	
	this.loadList();
}

/**
 * 载入列表
 */
listTable.loadList = function () {
	var args = this.compileFilter();
	var _this = this;
	$.ajax({
		type : "POST",
		url : listTable.url,
		data : args,
		dataType : "json",
		success : function (json) {
			_this.listCallback(json);
		}
	});
}

listTable.gotoPageFirst = function () {
	if (listTable.filter.page > 1) {
		listTable.gotoPage(1);
	}
}

listTable.gotoPagePrev = function () {
	if (listTable.filter.page > 1) {
		listTable.gotoPage(listTable.filter.page - 1);
	}
}

listTable.gotoPageNext = function () {
	if (listTable.filter.page < listTable.pageCount || listTable.filter.page < listTable.filter.page_count) {
		listTable.gotoPage(parseInt(listTable.filter.page) + 1);
	}
}

listTable.gotoPageLast = function () {
	if (listTable.filter.page < listTable.pageCount) {
		listTable.gotoPage(listTable.pageCount);
	}
}

listTable.listCallback = function (result, txt) {
	if (result.error > 0) {
		alert(result.message);
	} else {
		try {
			document.getElementById('listDiv').innerHTML = result.content;
			
			if (typeof result.filter == "object") {
				//console.log(result.filter);
				listTable.filter = result.filter;
			}
			
			listTable.pageCount = result.page_count;
			if(typeof listTable.call_func == "function"){
				listTable.call_func();
			}
			
			if($("#checkAll").length > 0){
				$("#checkAll").click(function(e){
					$("input[name='" + $(this).attr("for") + "']").attr("checked", this.checked);
				});
			}
			
		} catch (e) {
			alert(e.message);
		}
	}
}

listTable.selectAll = function (obj, chk) {
	if (chk == null) {
		chk = 'checkboxes';
	}
	
	var elems = obj.form.getElementsByTagName("INPUT");
	
	for (var i = 0; i < elems.length; i++) {
		if (elems[i].name == chk || elems[i].name == chk + "[]") {
			elems[i].checked = obj.checked;
		}
	}
}

listTable.compileFilter = function () {
	var args = '';
	for (var i in this.filter) {
		if (typeof(this.filter[i]) != "function" && typeof(this.filter[i]) != "undefined") {
			args += "&" + i + "=" + encodeURIComponent(this.filter[i]);
		}
	}
	
	return args;
}
