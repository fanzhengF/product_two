/*
	搜索查询，新标签打开
	增加刷新按钮
*/
$(document).ready(function () {

	var addReloadBtn = function () {
		var td = $('table.newfont03 td:first');
		if (td.length > 0) {
			td.append('<button type="button" onclick="location.reload();">刷新</button>');
		}
	};

	var init = function () {
		var form = $('form');
		if (form.length > 0) {
			if (self != top) {
				form.attr('target', '_blank');
			}
		}
		addReloadBtn();

	}

	init();
	
});	