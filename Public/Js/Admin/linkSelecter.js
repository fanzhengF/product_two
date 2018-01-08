/*
下拉组件
参数及调用方式：
 var config = {
    "obj" : JQuery  // 联动共用的
    "type" : 0,   // 0地区，1专业
    'lan' : 'en',  // 默认为cn中文
    "group" :[$('.s1'),$('.s2'),$('.s3')],
    "selected":[{'value':'86261010','text':'河南'},{'value':'86262210','text':'三门峡'},{'value':'86262211','text':'湖滨区'}]
  };
*/
// linkAreaOption.init(config);
var DATAS_SELECTS = [];
(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function($) {
    var pluses = /\+/g;

    function encode(s) {
        return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape...
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
            // Replace server-side written pluses with spaces.
            // If we can't decode the cookie, ignore it, it's unusable.
            s = decodeURIComponent(s.replace(pluses, ' '));
        } catch (e) {
            return;
        }

        try {
            // If we can't parse the cookie, ignore it, it's unusable.
            return config.json ? JSON.parse(s) : s;
        } catch (e) {}
    }

    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }

    var config = $.cookie = function(key, value, options) {

        // Write
        if (value !== undefined && !$.isFunction(value)) {
            options = $.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires,
                    t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            return (document.cookie = [
                encode(key), '=', stringifyCookieValue(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // Read

        var result = key ? undefined : {};

        // To prevent the for loop in the first place assign an empty array
        // in case there are no cookies at all. Also prevents odd result when
        // calling $.cookie().
        var cookies = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = parts.join('=');

            if (key && key === name) {
                // If second argument (value) is a function it's a converter...
                result = read(cookie, value);
                break;
            }

            // Prevent storing a cookie that we couldn't decode.
            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie;
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function(key, options) {
        if ($.cookie(key) !== undefined) {
            // Must not alter options, thus extending a fresh object...
            $.cookie(key, '', $.extend({}, options, {
                expires: -1
            }));
            return true;
        }
        return false;
    };

}));
var __QZ_GLOBAL_CONFIG__ = (function() {
    function config() {
        this.errorReportUrl = null;
        this.param = {};
        this.dataLoader = {};
    }
    config.prototype.onRegularDataLoaded = function(data) {
        var dataP = {};
        (function getParentRalationDatas(data, pVal) {
            for (var key in data) {
                if (dataP[key] == undefined) {
                    dataP[key] = {};
                    dataP[key].p = [];
                    dataP[key].c = false;
                }
                dataP[key].n = data[key].n;
                dataP[key].v = key;
                pVal == undefined ? null : dataP[key].p.push(pVal);
                if (!dataP[pVal]) {
                    dataP[pVal] = {};
                    dataP[pVal].p = [];
                }
                dataP[pVal].c = true;

                if (data[key].c != undefined) {
                    getParentRalationDatas(data[key].c, key);
                }
            }
        })(data);
        return {
            data: data,
            dict: dataP
        };
    };
    return new config();
})();
(function($) {
    function PageInfo(config) {
        this._config = config;
        this._param = {};
        this._urlParam = null;
        this._zindex = 10000;
        this._objId = 1000;
        this._data = {};
        this._init();
        if (this._config && this._config.param) {
            for (var i in this._config.param) {
                this.setParameter(i, this._config.param[i]);
            }
        }
    }
    PageInfo.prototype.POSITION = {
        LEFT: 'LEFT',
        RIGHT: 'RIGHT',
        CENTER: 'CENTER',
        TOP: 'TOP',
        MIDDLE: 'MIDDLE',
        BOTTOM: 'BOTTOM'
    };
    PageInfo.prototype.setParameter = function(k, v) {
        this._param[k] = v;
    };
    PageInfo.prototype.getParameter = function(k) {
        return this._param[k];
    };
    PageInfo.prototype.getUrlParameter = function(name) {
        if (this._urlParam == null) {
            this.getUrlParameters();
        }
        return this._urlParam[name];
    };
    PageInfo.prototype.getUrlParameters = function() {
        if (this._urlParam == null) {
            this._urlParam = {};
            var url = location.href;
            var arr = url.substring(url.indexOf("?") + 1, url.length).split("&");
            for (var i in arr) {
                var arrParam = arr[i].split('=');
                if (arrParam.length == 2) {
                    this._urlParam[arrParam[0]] = unescape(arrParam[1]);
                }
            }
        }
        return this._urlParam;
    };
    PageInfo.prototype.getPopZindex = function() {
        return this._zindex++;
    };
    /**
     * 鑾峰彇瀵硅薄鍧愭爣浣嶇疆锛岀敤浜庡脊鍑哄眰灞呬腑鎴栬彍鍗曠瓑
     * @param options = {
        obj : 闇€瑕佽幏鍙栦綅缃殑瀵硅薄
        relative : 浣嶇疆鐩稿瀵硅薄锛岄粯璁�$(window)
        xType : x杞寸浉瀵逛綅缃� ($.page.POSITION),榛樿$.page.POSITION.CENTER
        yType : y杞寸浉瀵逛綅缃� ($.page.POSITION),榛樿$.page.POSITION.MIDDLE
        xMargin : x杞翠綅缃┖鍑�
        yMargin : y杞翠綅缃┖鍑�
        limit : [涓�,鍙�,涓�,宸 闄愬埗浣嶇疆鏈€澶ц寖鍥达紝榛樿鎸夌収window绐椾綋澶у皬
        limitT : 闄愬埗鏈€澶ц寖鍥翠笂
        limitR : 闄愬埗鏈€澶ц寖鍥村彸
        limitB : 闄愬埗鏈€澶ц寖鍥翠笅
        limitL : 闄愬埗鏈€澶ц寖鍥村乏
        reverse : 瓒呰繃鑼冨洿澶勭悊鏂瑰紡 (true:鍙嶈浆,false:鎸夌収瓒呰繃鏁伴噺鍥炵Щ)锛岄粯璁rue
      }
     * @return {x:x鍧愭爣,y:y鍧愭爣}
     */
    PageInfo.prototype.getPosition = function(options) {
        var _this = this;
        var xType = options.xType || _this.POSITION.CENTER,
            yType = options.yType || _this.POSITION.MIDDLE;
        if (options.xType == 0) {
            xType = 0;
        }
        if (options.yType == 0) {
            yType = 0;
        }
        var obj = $(options.obj);
        var relaP, winObj = $(window),
            winP = {
                T: winObj.scrollTop(),
                L: winObj.scrollLeft(),
                H: winObj.height(),
                W: winObj.width()
            };
        var objP = {
            H: obj.outerHeight(true),
            W: obj.outerWidth(true)
        };
        if (options.relative) {
            var relaObj = $(options.relative);
            var relaOffset = relaObj.offset();
            relaP = {
                H: relaObj.outerHeight(true),
                W: relaObj.outerWidth(true),
                L: relaOffset.left,
                T: relaOffset.top
            };
        } else {
            relaP = winP;
        }
        if (options.xMargin) {
            relaP.L += options.xMargin;
        }
        if (options.yMargin) {
            relaP.T += options.yMargin;
        }
        var limitP = [0, winP.L + winP.W, winP.H + winP.T, 0],
            isReverse = true;
        if (options.limit) {
            if (options.limit.length == 1) {
                limitP[0] = limitP[1] = limitP[2] = limitP[3] = options.limit[0];
            } else if (options.limit.length == 2) {
                limitP[0] = limitP[2] = options.limit[0];
                limitP[1] = limitP[3] = options.limit[1];
            } else {
                for (var i in options.limit) {
                    if (i == 4) {
                        break;
                    }
                    if (options.limit[i] !== null) {
                        limitP[i] = options.limit[i];
                    }
                }
            }
        }
        if (options.limitT) {
            limitP[0] = options.limitT;
        }
        if (options.limitR) {
            limitP[1] = options.limitR;
        }
        if (options.limitB) {
            limitP[2] = options.limitB;
        }
        if (options.limitL) {
            limitP[3] = options.limitL;
        }
        if (options.reverse != undefined && options.reverse != null) {
            isReverse = options.reverse;
        }
        var p = getPosition(objP, relaP, xType, yType, limitP, isReverse);
        var parentOffset = getParentOffset(obj);
        p.x -= parentOffset.left;
        p.y -= parentOffset.top;
        return p;

        function getParentOffset(obj) {
            var parent = obj.parent();
            if (parent.is('body')) {
                return parent.offset();
            }
            if (parent.css('position') == 'static') {
                return getParentOffset(parent);
            } else {
                return parent.offset();
            }
        }

        function getPosition(objP, relaP, xType, yType, limitP, isReverse) {
                var x, y;
                if (xType == _this.POSITION.LEFT) {
                    x = relaP.L - objP.W;
                } else if (xType == _this.POSITION.RIGHT) {
                    x = relaP.L + relaP.W;
                } else if (xType == _this.POSITION.CENTER) {
                    x = relaP.L + relaP.W / 2 - objP.W / 2;
                } else {
                    x = relaP.L + xType;
                }
                if (yType == _this.POSITION.TOP) {
                    y = relaP.T - objP.H;
                } else if (yType == _this.POSITION.MIDDLE) {
                    y = relaP.T + relaP.H / 2 - objP.H / 2;
                } else if (yType == _this.POSITION.BOTTOM) {
                    y = relaP.T + relaP.H;
                } else {
                    y = relaP.T + yType;
                }
                if (xType != _this.POSITION.CENTER) {
                    x = calcute(x, objP.W, relaP.W, relaP.L, limitP[1], limitP[3], isReverse, options.xMargin || 0);
                }
                if (yType != _this.POSITION.MIDDLE) {
                    y = calcute(y, objP.H, relaP.H, relaP.T, limitP[2], limitP[0], isReverse, options.yMargin || 0);
                }
                if (x < 0) {
                    if (isReverse && xType != _this.POSITION.CENTER) {
                        x = relaP.L + relaP.W;
                    } else {
                        x = 0;
                    }
                }
                if (y < 0) {
                    if (isReverse && yType != _this.POSITION.MIDDLE) {
                        y = relaP.T + relaP.H;
                    } else {
                        y = 0;
                    }
                }
                return {
                    x: x,
                    y: y
                };
            }
            // V1 : H,W,right,bottom  V2 : T,L,top,left
        function calcute(position, objV1, relaV1, relaV2, limitV1, limitV2, isReverse, margin) {
            //涓嬭秴楂� 鍙宠秴瀹�
            if (position + objV1 > limitV1) {
                if (isReverse) {
                    position = relaV2 - objV1 - 2 * margin;
                } else {
                    position = limitV1 - objV1;
                }
            }
            //涓婅秴楂� 宸﹁秴瀹�
            if (position < limitV2) {
                if (isReverse) {
                    position = relaV2 + relaV1 + 2 * margin;
                } else {
                    position = limitV2;
                }
            }

            return position;
        }
    };
    PageInfo.prototype.getObjectId = function() {
        this._objId++;
        return 'qzobj_' + this._objId;
    };
    PageInfo.prototype.regReplace = function(str, data, reg) {
        if (!reg) {
            reg = /\{\w*\}/g;
        }
        return str.replace(reg, function(match, pos) {
            var s = '';
            var command = match.substring(1, match.length - 1);
            if (data[command]) {
                s = data[command];
            }
            return s;
        });
    };
    PageInfo.prototype.getData = function(dataType, callback) {
        if (!this._data[dataType]) {
            var config = dataType;
            if (this._config.dataLoader && this._config.dataLoader[dataType]) {
                config = this._config.dataLoader[dataType];
            }
            this._data[dataType] = new dataLoader(config);
        }
        this._data[dataType].addCallback(callback);
    };
    PageInfo.prototype.addDataLoaderSettings = function(dataType, settings) {
        if (!this._config.dataLoader) {
            this._config.dataLoader = {};
        }
        this._config.dataLoader[dataType] = settings;
    };
    PageInfo.prototype.getStringWidth = function(str, fontSize) {
        var objId = 'qzobj_getwidth';
        var span = document.getElementById(objId);
        if (span == null) {
            span = document.createElement("span");
            span.id = objId;
            document.body.appendChild(span);
            span.style.visibility = "hidden";
            span.style.whiteSpace = "nowrap";
        }
        span.innerHTML = str;
        span.style.fontSize = fontSize + "px";

        return span.offsetWidth;
    };
    PageInfo.prototype.getDataLoaderSettings = function(dataType) {
        var settings = null;
        if (this._config.dataLoader && this._config.dataLoader[dataType]) {
            settings = this._config.dataLoader.settings[dataType];
        }
        return settings;
    };
    PageInfo.prototype._init = function() {
        var _this = this;
        // ajax杩斿洖鏍囧噯鏁版嵁鏍煎紡瀹氫箟
        $.ajaxSetup({
            dataType: 'json',
            success: function(data, textStatus, jqXHR) {
                if ($.isPlainObject(data)) {
                    if (data.error && this.error) {
                        this.error(data.error.code, data.error.message);
                    } else {
                        if (this.callback) {
                            this.callback(data.result, textStatus, jqXHR);
                        }
                    }
                } else {
                    if (this.callback) {
                        this.callback(data, textStatus, jqXHR);
                    }
                }
            }
        });
    };

    function dataLoader(config) {
        this._config = config;
        this._callbacks = [];
        this._finished = false;
        this._data = null;
        this._load();
    }
    dataLoader.prototype.addCallback = function(callback) {
        if (this._finished) {
            callback(this._data);
        } else {
            this._callbacks.push(callback);
        }
    };
    dataLoader.prototype._load = function() {
        var _this = this;
        var param = {
            dataType: 'json',
            callback: function(data) {
                if (_this._config.loaded) {
                    _this._data = _this._config.loaded(data);
                } else {
                    _this._data = data;
                }
                _this._finished = true;
                while (_this._callbacks.length) {
                    var callback = _this._callbacks.shift();
                    callback(_this._data);
                }
            }
        };
        if (typeof _this._config == 'string') {
            param.url = _this._config;
        } else {
            for (var i in _this._config) {
                // 闃叉瑕嗙洊蹇呰鐨刟jax鍙傛暟
                if ((i != 'callback') && (i != 'success')) {
                    param[i] = _this._config[i];
                }
            }
        }
        _this._finished = false;
        $.ajax(param);
    };
    jQuery.extend({
        page: new PageInfo(__QZ_GLOBAL_CONFIG__)
    });
})(jQuery);
var isArray = function(obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
}


//   //
var linkAreaOption = function(config) {
    var _this = this;
    var _config = {
        type: 0,
        dataType: ['area', 'major'],
        lan: 'cn',
        unedit: false,
        selectText: {
            en: "select",
            cn: "请选择"
        },
        group: false,
        selected: false,
        data: null,
        changeFunction: null
    };
    for (var i in config) {
        _config[i] = config[i];
    }
    this._config = _config;
    if (config.type == 1) {
        var dataP = {};
        var data = {};
        if (this._config.lan == 'cn') {
            data = obj_select;
        } else {
            data = obj_select;
        }

        (function getParentRalationDatas(data, pVal) {
            for (var key in data) {
                if (dataP[key] == undefined) {
                    dataP[key] = {};
                    dataP[key].p = [];
                    dataP[key].c = false;
                }
                dataP[key].n = data[key].n;
                dataP[key].v = key;
                pVal == undefined ? null : dataP[key].p.push(pVal);
                if (!dataP[pVal]) {
                    dataP[pVal] = {};
                    dataP[pVal].p = [];
                }
                dataP[pVal].c = true;

                if (data[key].c != undefined) {
                    getParentRalationDatas(data[key].c, key);
                }
            }
        })(data);
        _this.data = data;
        _this.dataP = dataP;
        _this.initCityOption();
        // if(_this._config.unedit){
        _this.bindCityOptionEvent();
        // }
        if (_this._config.selected && _this._config.selected.length && _this._config.selected[0].value) {
            for (var k = 0; k < _this._config.selected.length; k++) {
                _this._config.selected[k].text = _this.dataP[_this._config.selected[k].value].n
            }
        }
    } else {
        this._config.dataType = this._config.dataType[this._config.type] + '_' + this._config.lan;
        $.page.getData("/Admin/Access/getCate", function(datas) {
            var obj_select = {};
            var parents = datas[0];
            var parent = datas[1];
            var children = datas[2];
            for (var i in parents) {
                obj_select[parents[i].id] = {
                    n: parents[i].name
                }
            }

            function flushObj(obj, parent) {
                for (var key in obj) {
                    for (var j in parent[key]) {
                        if (!obj[key].c) {
                            obj[key].c = {};
                        }
                        var tem = {};
                        obj[key].c[parent[key][j]["id"]] = {};
                        obj[key].c[parent[key][j]["id"]] = {
                            n: parent[key][j].name
                        };
                    }
                }
            }

            function flush(obj, parent) {
                for (var j in parent) {
                    for (var key in parent[key]) {
                        if (!obj[key].c) {
                            obj[key].c = {};
                        }
                        var tem = {};
                        obj[key].c[parent[key][j]["id"]] = {};
                        obj[key].c[parent[key][j]["id"]] = {
                            n: parent[key][j].name
                        };
                    }
                }
            }
            flushObj(obj_select, parent);
            for (var p in children) {
                for (var q in children[p]) {
                    var t = children[p][q];
                    for (var w in t) {
                        if (obj_select[p]) {
                            if (obj_select[p].c[q]) {
                                if (!obj_select[p].c[q].c) {
                                    obj_select[p].c[q].c = {};
                                    obj_select[p].c[q].c["n"] = t[w].name;
                                }

                                obj_select[p].c[q].c[w] = {};
                                obj_select[p].c[q].c[w].n = t[w].name;
                            }
                        }

                    }

                }
            }
            var dataP = {};
            var data = obj_select;

            (function getParentRalationDatas(data, pVal) {
                for (var key in data) {
                    if (dataP[key] == undefined) {
                        dataP[key] = {};
                        dataP[key].p = [];
                        dataP[key].c = false;
                    }
                    dataP[key].n = data[key].n;
                    dataP[key].v = key;
                    pVal == undefined ? null : dataP[key].p.push(pVal);
                    if (!dataP[pVal]) {
                        dataP[pVal] = {};
                        dataP[pVal].p = [];
                    }
                    dataP[pVal].c = true;

                    if (data[key].c != undefined) {
                        getParentRalationDatas(data[key].c, key);
                    }
                }
            })(data);
            _this.data = data;

            _this.dataP = dataP;
            _this.initCityOption();
            // if(!_this._config.unedit){
            _this.bindCityOptionEvent();
            // }
            if (_this._config.selected && _this._config.selected.length && _this._config.selected[0].value) {
                for (var k = 0; k < _this._config.selected.length; k++) {
                    _this._config.selected[k].text = _this.dataP[_this._config.selected[k].value].n
                }
            }
        });
    }
};
linkAreaOption.init = function(config) {
    config.obj.linkAreaOption = new linkAreaOption(config);
    return config.obj.linkAreaOption;
};
linkAreaOption.prototype.initCityOption = function() {
    var _this = this;
    var data = _this.data;
    var obj1 = _this._config.group[0];
    var obj2 = _this._config.group[1];
    var obj3 = _this._config.group[2];
    var tem = [];
    if (_this._config.group) {
        tem.push('<option value="' + _this._config.selectText[_this._config.lan] + '" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>');
        obj1.html(" ");
        for (var i in data) {
            tem.push('<option value="' + i + '" title="' + data[i].n + '">' + data[i].n + '</option>');
        }
        obj1.html(tem.join(""));
        if (!_this._config.selected) {
            obj1.val(_this._config.selectText[_this._config.lan]);
        }
        if (_this._config.selected && (typeof _this._config.selected[0] == 'object') && _this._config.selected[0].value) {
            obj1.val(_this._config.selected[0].value);

            // 解决闪惠购二级经营类别 编辑时隐藏的input不能正常赋值问题 by 李文瑞 start
            obj1.parent().find("input").val(_this._config.selected[0].value);
            // 解决闪惠购二级经营类别 编辑时隐藏的input不能正常赋值问题 by 李文瑞 end
            if (_this._config.unedit) {
                obj1.attr('disabled', 'disabled');
            }
        }
        if (obj2) {
            var temSec = [];
            temSec.push('<option value="' + _this._config.selectText[_this._config.lan] + '" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>');
            if (_this._config.selected && (_this._config.selected.length > 0) && (typeof _this._config.selected[0] == 'object') && _this._config.selected[0].value) {
                var dataSec = _this.data[_this._config.selected[0].value].c;
                if (dataSec) {
                    for (var k in dataSec) {
                        temSec.push('<option value="' + k + '" title="' + dataSec[k].n + '">' + dataSec[k].n + '</option>');
                    }
                    obj2.html(temSec.join(""));
                }
                if (_this._config.selected.length > 0 && (typeof _this._config.selected[1] == 'object') && _this._config.selected[1].value) {
                    obj2.val(_this._config.selected[1].value);
                    // 解决闪惠购二级经营类别 编辑时隐藏的input不能正常赋值问题 by 李文瑞
                    obj2.parent().find("input").val(_this._config.selected[1].value);
                    // 解决闪惠购二级经营类别 编辑时隐藏的input不能正常赋值问题 by 李文瑞 end
                }
                if (dataSec) {
                    obj2.removeAttr('disabled');
                } else {
                    obj2.attr('disabled', 'disabled');
                }
            } else {
                obj2.html(temSec.join(""));
                obj2.attr('disabled', 'disabled');
                if (!_this._config.selected) {
                    obj2.val(_this._config.selectText[_this._config.lan]);
                }
            }
            if (_this._config.unedit) {
                obj2.attr('disabled', 'disabled');
            }
        }
        if (obj3) {
            var temTh = [];
            temTh.push('<option value="' + _this._config.selectText[_this._config.lan] + '" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>');
            if (_this._config.selected && _this._config.selected.length > 1) {
                var dataTh = _this.data[_this._config.selected[0].value].c[_this._config.selected[1].value].c;
                if (dataTh) {
                    for (var k in dataTh) {
                        if (typeof dataTh[k] != "string") {
                            temTh.push('<option value="' + k + '" title="' + dataTh[k].n + '">' + dataTh[k].n + '</option>');
                        }
                    }
                }
                obj3.html(temTh.join(""));
                if (_this._config.selected.length > 2) {
                    obj3.val(_this._config.selected[2].value);
                    obj3.parent().find("input").val(_this._config.selected[2].value);
                }
                if (dataTh) {
                    obj3.removeAttr('disabled');
                } else {
                    obj3.attr('disabled', 'disabled');
                }
            } else {
                obj3.html(temTh.join(""));
                obj3.attr('disabled', 'disabled');
                if (!_this._config.selected) {
                    obj3.val(_this._config.selectText[_this._config.lan]);
                }
            }
            if (_this._config.unedit) {
                obj3.attr('disabled', 'disabled');
            }
        }
    }
};
linkAreaOption.prototype.bindCityOptionEvent = function() {
    var _this = this;
    for (var i = 0; i < _this._config.group.length; i++) {
        _this._config.group[i].change(function() {
            var index = 0;
            var value = $(this).val();
            var nextObj = null;
            var lastObj = null;
            var tem = [];
            for (var j = 0; j < _this._config.group.length; j++) {
                if ($(this).is(_this._config.group[j])) {
                    index = j;
                }
            }
            nextObj = _this._config.group[index + 1];
            lastObj = _this._config.group[index + 2];
                //给input赋值
                $(this).parent().find("input").val(value);
            if ($(this).val() && $(this).val() != _this._config.selectText[_this._config.lan]) {
                $(this).val($(this).val());
                if (nextObj) {
                    if (index == 0) {
                        var datas = _this.data[value].c;
                    } else {
                        var datas = _this.data[_this.dataP[value].p[0]].c[value].c;
                    }
                    tem.push('<option value="" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>');
                    if (datas) {
                        for (var k in datas) {
                            if (datas[k].n) {
                                tem.push('<option value="' + k + '" title="' + datas[k].n + '">' + datas[k].n + '</option>');
                            }
                        }
                        nextObj.html(tem.join("")).removeAttr('disabled');
                    } else {
                        nextObj.html('<option value="" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>').attr('disabled', 'disabled');;
                    }
                }
                if (lastObj) {
                    lastObj.html('<option value="" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>').attr('disabled', 'disabled');
                }

            } else {
                if (nextObj) {
                    nextObj.html('<option value="" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>').attr('disabled', 'disabled');

                }
                if (lastObj) {
                    lastObj.html('<option value="" title="' + _this._config.selectText[_this._config.lan] + '">' + _this._config.selectText[_this._config.lan] + '</option>').attr('disabled', 'disabled');
                }
            }
            if (_this._config.changeFunction) {
                setTimeout(function() {
                    _this._config.changeFunction(index, value);
                }, 600);
            }
        });
    }
};