var sradar_proRadar;
var sradar_iframeObj;
var sradar_index = 0;
var sradar_JSONP = {
    getSprite   :   function(src,callback){
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.onload = script.onreadystatechange = function(){
            //onreadystatechange，仅IE
            if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
                callback && callback.call(this);
                script.onload = script.onreadystatechange = null;//请内存，防止IE memory leaks
            }
        }
        script.src = src;
        document.getElementsByTagName("head")[0].appendChild(script);
        return script;
    },
    oncomplete: function(){}
}

function radar_point_extract(){
    if(!sradar_proRadar){
        sradar_proRadar = new sradar_ProductRadar();
    }else{
        sradar_proRadar.removeSearchButton();
        sradar_proRadar.initialize();
    }
}
var sradar_ProductRadar = function(){
    this.initialize();
    this.onlyOne();
    if(!sradar_iframeObj){
        sradar_iframeObj = new sradar_Iframe();
    }
}
sradar_ProductRadar.prototype = {
    initialize  :   function(){
        var self = this;
        var product_list,
            product_area,
            lincense_id,
            product_key,
            sign_key;

        sradar_index = 0;
        var currentPage = 1;
        var currentIndex = 0;
        var iframeUrl_arr = [];
        var searchButton_arr = [];
        var lastPoint = null;
        var script_arr = [];

        product_list = self.$class("radar_product_point");
        if(!product_list) return;
        lincense_id = self.$id("radar_lincense_id") ? self.$id("radar_lincense_id").value : 0;
        product_key = self.$id("radar_product_key") ? self.$id("radar_product_key").value : 0;
        sign_key = self.$id("radar_sign_key") ? self.$id("radar_sign_key").value : 0;

        var theUrl = "http://plugin.sradar.cn/index.php/Index/index/s/";
        //var theUrl = "http://plugin.229.cn/cloud/sradar/index.php/Index/index/s/";

        //var sradar_src = "http://192.168.65.229/cloud/sradar/index.php/Plugin/GoodsApi/newFun/lincense_id/"+lincense_id;
        var sradar_src = "http://plugin.sradar.cn/index.php/GoodsApi/newFun/lincense_id/"+lincense_id;
        var sradar_isNewFun;
        sradar_JSONP.getSprite(sradar_src,function(){
            sradar_isNewFun = sradar_isNew;
            loader();
        });

        function loader(){
            var _keyword = self.getNodeValue(product_list[sradar_index],"radar_keyword");
                var keyword = _keyword ? self.clearString(_keyword):"";

            var _name = self.getNodeValue(product_list[sradar_index],"radar_name");
                var name = _name ? self.clearString(_name):"";

            var _price = self.getNodeValue(product_list[sradar_index],"radar_price");
                var price = _price ? _price:"";

            var _product_id = self.getNodeValue(product_list[sradar_index],"radar_product_id");
                var product_id = _product_id ? self.clearString(_product_id):"";

            var _brand = self.getNodeValue(product_list[sradar_index],"radar_brand");
                var brand = _brand ? self.clearString(_brand):"";

            var _sn = self.getNodeValue(product_list[sradar_index],"radar_sn");
                var sn = _sn ? self.clearString(_sn):"";

            var _catname = self.getNodeValue(product_list[sradar_index],"radar_catname");
                var catname = _catname ? self.clearString(_catname):"";

            var send_data  = {"lincense_id":lincense_id,"keyword":keyword,"name":name,"brand":brand,"sn":sn,"catname":catname,"price":price,"product_id":product_id,"product_key":product_key};
            var send_data2 = {"lincense_id":lincense_id,"keyword":keyword,"name":name,"brand":brand,"sn":sn,"catname":catname,"price":price,"product_id":product_id,"product_key":product_key,"sign_key":sign_key};
            iframeUrl_arr.push(send_data2);

            var match_num = 0;
            var offerCount = 0;
            var merchantCount = 0;
            var priceChange = false;

            var src = "http://plugin.sradar.cn/index.php/GoodsApi/index/jsoncallback/sradar_JSONP.oncomplete.request_" + sradar_index+"/s/" + encodeURIComponent(self.encode(send_data))+"/ran/" + Math.random();
			//var src = "http://plugin.229.cn/index.php/GoodsApi/index/jsoncallback/sradar_JSONP.oncomplete.request_" + sradar_index+"/s/" + encodeURIComponent(self.encode(send_data))+"/ran/" + Math.random();
            
            var script = sradar_JSONP.getSprite(src);
            script_arr.push(script);

            sradar_JSONP.oncomplete['request_' + sradar_index] = function(data){
                if(!data) return;
                if(!self.$id('r'+product_id))return;

                var t_radar_point_inside = data[0].count;
                var t_radar_point_title = data[0].title;
                priceChange = data[0].priceChange;

                var t_title = '<span style="float:left;text-align:left;padding-top:6px;">商品雷达-竞品情报系统</span>';
                    t_title+= '<span style="color:#ADADAD;font-size:10px;float:left;text-align:left;padding-top:5px;">beta</span>';
                    t_title+= '<span style="color:#ADADAD;font-size:11px;_display:inline;float:left;text-align:left;margin-left:2px;">';
                    t_title+= '<em title="查看上一个商品" id="radar_pref_point" class="radar_change_action_up_alive" onclick="prevpage()"></em>';
                    t_title+= '<em title="查看下一个商品" id="radar_next_point" class="radar_change_action_down_alive" onclick="nextpage()"></em>';
                    t_title+= '<input type="hidden"  id="radar_current_point_key" value="'+ sradar_index +'" />';
                    t_title+= '</span>';
                var radar_title_tips= '&nbsp;<button class="popup-btn-maximize" onclick="sradar_iframeObj.setResize()" hidefocus="" title="最大化" type="button">1</button>';
                    radar_title_tips+= '<button class="popup-btn-close" hidefocus="" onclick="closeWindow()" title="关闭" type="button">0</button>';

                sradar_iframeObj.setTitleLeft(t_title);
                sradar_iframeObj.setTitleRight(radar_title_tips);

                var search_button = document.createElement("span");
                search_button.className = priceChange ? "radar_product_point_area_price_change" :"radar_product_point_area";
                search_button.title = t_radar_point_title;
                search_button.index = sradar_index;
                //var fontElem = document.createElement("span");
                //fontElem.className = priceChange ? 'radar_num_attention_font':'radar_num_font';
                //fontElem.innerHTML = t_radar_point_inside;
                var new_div = document.createElement('div');
                new_div.className = 'radar_num_isnew';
                new_div.style.display = sradar_isNewFun ? 'block' : 'none';
                search_button.appendChild(new_div);
                //search_button.appendChild(fontElem);
                searchButton_arr.push(search_button);
                
                if(		!self.$class('radar_product_point_area', self.$id('r'+product_id).parentNode).length 
                		&& !self.$class('radar_product_point_area_price_change', self.$id('r'+product_id).parentNode).length 
                		&& !self.$class('radar_point_selected_price_change', self.$id('r'+product_id).parentNode).length 
                		&& !self.$class('radar_point_selected', self.$id('r'+product_id).parentNode).length) self.$id('r'+product_id).parentNode.insertBefore(search_button,self.$id('r'+product_id).nextSibling);

                search_button.onclick = function(){
                    if(lastPoint == null){
                        lastPoint = searchButton_arr[0];
                    }
                    currentIndex = this.index;
                    cutover();
                }

                if(sradar_index > product_list.length-1){
                    for(var n=0;n<script_arr.length;n++){
                        if(script_arr[n])document.getElementsByTagName("head")[0].removeChild(script_arr[n]);
                    }
                    //sradar_ifRefresh = true;
                }else{
                    sradar_index++;
                    loader();
                }

            }
        }

        prevpage = function(){
            this.cancelBubble = true;
            currentIndex--;
            if(currentIndex <= -1) currentIndex = product_list.length - 1;
            cutover();
        }
        nextpage = function(){
            this.cancelBubble = true;
            currentIndex++;
            if(currentIndex >= product_list.length) currentIndex = 0;
            cutover();
        }
        cutover = function(){
            var url = theUrl + encodeURIComponent(self.encode(iframeUrl_arr[currentIndex]));
            sradar_iframeObj.updataIframe(url);
            if(searchButton_arr[currentIndex].className == 'radar_product_point_area_price_change'){
            	searchButton_arr[currentIndex].className = 'radar_point_selected_price_change';
            }else{
            	searchButton_arr[currentIndex].className = 'radar_point_selected';
            }
			if(lastPoint){
				if(lastPoint != searchButton_arr[0] || (lastPoint == searchButton_arr[0] && currentIndex != 0)){
					if(lastPoint.className == 'radar_point_selected_price_change'){
						lastPoint.className = 'radar_product_point_area_price_change';
					}
					if(lastPoint.className == 'radar_point_selected'){
						lastPoint.className = 'radar_product_point_area';
					}
				}			
			}
            
            lastPoint = searchButton_arr[currentIndex];
        }
        closeWindow = function(){
			if(lastPoint){
				if(lastPoint.className == 'radar_point_selected_price_change'){
					lastPoint.className = 'radar_product_point_area_price_change';
				}
				if(lastPoint.className == 'radar_point_selected'){
					lastPoint.className = 'radar_product_point_area';
				}			
			}

            sradar_iframeObj.setClose();
        }

    },
    onlyOne :   function(){
        var css = document.createElement("link")
        css.media = "screen,projection";
        css.type = "text/css";
        css.href = "http://js.sradar.cn/radarForGoodsList.css";
        css.rel = "stylesheet";
        var headElem = document.getElementsByTagName("head")[0];
        headElem.appendChild(css);
    },
    removeSearchButton  :   function(){
        var self = this;
        if(sradar_iframeObj)sradar_iframeObj.setClose();
        var btnCurrent = self.$class("radar_product_point_area radar_point_selected")[0];
        if(btnCurrent)btnCurrent.parentNode.removeChild(btnCurrent);
        var btnList = self.$class("radar_product_point_area");
        for(var i=0;i<btnList.length;i++){
            var parentObj = btnList[i].parentNode;
            parentObj.removeChild(btnList[i]);
        }
    },
    encode  :   function(obj){
        var string = [];
        for(var i in obj){
            var json = obj[i];
            if(json) string.push("\"" + i + "\":\"" + json + "\"");
        }
        return "{" + string + "}";
    },
    toQueryString   :   function(obj){
        var str = "";
        var sign = "&";
        for(var i in obj){
            str += i + "=" + encodeURIComponent(obj[i]) + sign;
        }
        return str.substring(0,str.length-1);
    },
    getNodeValue    :   function(obj,node){
        return obj?obj.attributes[node].nodeValue:null;
    },
    $id :   function(id){
        return document.getElementById(id);
    },
    $class  :   function(sClass,scope){
        scope = scope || document;
        var aElem = scope.getElementsByTagName('*');
        var aClass = [];
        var i = 0;
        for(i=0;i<aElem.length;i++)if(aElem[i].className == sClass)aClass.push(aElem[i]);
        return aClass;
    },
    clearString :   function(s){
        if(!s) return;
        var pattern = new RegExp("[`~!@#$^&*()=|{}':;',\\[\\]<>/?~！@#￥……&*（）&;|{}【】‘；：”“'，、？.%]");
        s = s.replace(/<\/?[^>]*>/g,'');
		var rs ='';
        for (var i = 0; i < s.length; i++) {
            rs = rs+s.substr(i, 1).replace(pattern, ' ');
        }
        return rs;
    }

}

var sradar_Iframe = function(){
    this.initialize();
}
sradar_Iframe.prototype = {
    width   :   796,
    height  :   487,
    iframeBox   :   null,
    iframeTitle :   null,
    iframeContent   :   null,
    titleLeft   :   null,
    titleRight  :   null,
    winBoo  :   false,
    point   :   {},
    initialize  :   function(){
        var self = this;
        self.point.x = ((document.documentElement.clientWidth || document.body.clientWidth) - self.width)/2;
        self.point.y = ((document.documentElement.clientHeight || document.body.clientHeight) - self.height)/2;

        self.iframeBox = document.createElement("div");
        self.iframeBox.style.left = self.iframeBox.style.top = 0;
        self.iframeBox.className = "popup-body";
        self.iframeBox.width = self.width + "px";
        self.iframeBox.height = self.height + "px";
        self.iframeBox.style.left = self.point.x + "px";
        self.iframeBox.style.top = self.point.y + "px";
        self.iframeBox.style.display = "none";


        self.iframeTitle = document.createElement("div");
        self.iframeTitle.style.cursor = "move";
        self.iframeTitle.className = "popup-header";
        self.iframeBox.appendChild(self.iframeTitle);

        self.titleLeft = document.createElement("div");
        self.titleLeft.className = "popup-dialog-title clearfix";
        self.iframeTitle.appendChild(self.titleLeft);

        self.titleRight = document.createElement("div");
        self.titleRight.className = "r-area";
        self.iframeTitle.appendChild(self.titleRight);

        var bDrag = false;
        var disX = disY = 0;
        var aPos = [{x:self.iframeBox.offsetLeft, y:self.iframeBox.offsetTop}];
        self.iframeTitle.onmousedown = function (event){
            if(self.winBoo) return;
            var event = event || window.event;
            bDrag = true;
            disX = event.clientX - self.iframeBox.offsetLeft;
            disY = event.clientY - self.iframeBox.offsetTop;
            aPos.push({x:self.iframeBox.offsetLeft, y:self.iframeBox.offsetTop});
            this.setCapture && this.setCapture();
            return false;
        }
        document.onmousemove = function (event){
            if(self.winBoo) return;
            if (!bDrag) return;
            var event = event || window.event;
            var iL = event.clientX - disX;
            var iT = event.clientY - disY;
            var maxL = (document.documentElement.clientWidth || document.body.clientWidth) - self.iframeBox.offsetWidth;
            var maxT = (document.documentElement.clientHeight || document.body.clientHeight) - self.iframeBox.offsetHeight;
            iL = iL < 0 ? 0 : iL;
            iL = iL > maxL ? maxL : iL;
            iT = iT < 0 ? 0 : iT;
            iT = iT > maxT ? maxT : iT;
            self.iframeBox.style.marginTop = self.iframeBox.style.marginLeft = 0;
            self.iframeBox.style.left = iL + "px";
            self.iframeBox.style.top = iT + "px";
            aPos.push({x:iL, y:iT});
            self.point.x = iL;
            self.point.y = iT;
            return false;
        }
        document.onmouseup = window.onblur = self.iframeTitle.onlosecapture = function (){
            bDrag = false;
            self.iframeTitle.releaseCapture && self.iframeTitle.releaseCapture();
        }
        self.iframeTitle.ondblclick = function(){
            self.setResize();
        }

    },
    setTitleLeft    :   function(content){
        this.titleLeft.innerHTML = content;
    },
    setTitleRight   :   function(content){
        this.titleRight.innerHTML = content;
    },
    setResize : function(){
        var self = this;
        self.winBoo = !self.winBoo;
        if(!self.winBoo){
            self.iframeBox.style.width = self.width + 2 + "px";
            self.iframeBox.style.height = self.height + 35 + "px";
            self.iframeContent.width = self.width + "px";
            self.iframeContent.height = self.height + "px";
            self.iframeBox.style.left = self.point.x + "px";
            self.iframeBox.style.top = self.point.y + "px";
        }else{
            self.iframeBox.style.left = self.iframeBox.style.top = "0px";
            self.iframeBox.style.width = (document.documentElement.clientWidth || document.body.clientWidth) - 2 + "px";
            self.iframeBox.style.height = (document.documentElement.clientHeight || document.body.clientHeight) - 2 + "px";
            self.iframeContent.width = (document.documentElement.clientWidth || document.body.clientWidth) - 2 + "px";
            self.iframeContent.height = (document.documentElement.clientHeight || document.body.clientHeight) - 2 + "px";
        }
    },
    setClose    :   function(){
        this.iframeBox.style.display = "none";
    },
    updataIframe    :   function(url){
        var self = this;
        self.iframeBox.style.display = "block";
        if(self.iframeContent != null){
            self.iframeContent.src = url;
        }else{
            self.iframeContent = document.createElement("iframe");
            self.iframeContent.width = self.width + "px";
            self.iframeContent.height = self.height + "px";
            self.iframeContent.src = url;
            self.iframeContent.frameBorder = 0;
            self.iframeBox.appendChild(self.iframeContent);
            document.body.appendChild(self.iframeBox);
        }
    }
}


//window.onload = function() {
    //radar_point_extract();
//}
