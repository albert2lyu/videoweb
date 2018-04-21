
var POPUP = {
    getH:function(){
        if (window.innerHeight && window.scrollMaxY) {
            yScroll = window.innerHeight + window.scrollMaxY;
        } else if (document.body.scrollHeight > document.body.offsetHeight){
            yScroll = document.body.scrollHeight;
        } else {yScroll = document.body.offsetHeight;}
        return yScroll;       
    },
    pw:function(el){
        if(!document.getElementById(el)){return false;}
        if(!document.createElement){return false;}
        POPUP.cpw(el);
        if(POPUP.hasSlt()){POPUP.cifr();}
    },
    cpw:function(el){
        el= document.getElementById(el);       
        el.className = "popupdiv";
        el.style.marginLeft = "-" + el.clientWidth/2 + "px";
        pwb = document.createElement('div');
        pwb.setAttribute('id','popupbg');
        ph =el.clientHeight;
        sh = (window.innerHeight) ? window.innerHeight : (document.documentElement && document.documentElement.clientHeight) ? document.documentElement.clientHeight : document.body.offsetHeight;
        bh = POPUP.getH();
        if(ph>bh){
            el.style.marginTop = "-180px";
            pwb.style.height = ph+180+"px";
        }else{
            if(ph>sh){
                pwb.style.height = bh>ph?bh+'px':ph+'px';
                el.style.marginTop = '-180px';
            }else{       
                el.style.marginTop = "-" + ph/2 + 'px';
                pwb.style.height = sh>bh?sh+'px':bh+'px';
            }   
        }
        return document.body.appendChild(pwb);
    },
    cifr:function(){
        if(!document.createElement("iframe")){return false;};
        ifr = document.createElement("iframe");
        ifr.setAttribute("id","divshim");
        ifr.setAttribute("src","javascript:void(0);");
        ifr.setAttribute("scrolling","no");
        ifr.setAttribute("frameborder","0");
        ifr.setAttribute("allowtransparency","true");
        POPUP.insertAfter(ifr,pwb);
        ifr.className = "popupifra";
        ifr.style.height = pwb.style.height;
        ifr.style.width = pwb.style.width;       
    },
    insertAfter:function(nEl,tEl) {
      var p = tEl.parentNode;if (p.lastChild == tEl) {p.appendChild(nEl);
      } else {p.insertBefore(nEl,tEl.nextSibling); }
    },
    hasSlt:function(){
        var a = document.getElementsByTagName('*');
        var hasSlt = false;
        for(var i=a.length-1;i>=0;i--){
            if(a[i].nodeName == 'SELECT'){hasSlt = true;break;}
        }
        return hasSlt;
    },
    attr:function(el,name,value){
        if(!name || name.constructor != String){return '';}
        name = {'for':'htmlFor','class':'className'}[name] || name;
        if(typeof value != 'undefined'){
            el[name] = value;
            if(el.setAttribute){el.setAttribute(name,value);}
            return el[name] || el.getAttribute(name) || '';
        }
    },
    cw:function(el){
    pwb.className = "hidden";
    document.getElementById(el).className = "hidden";   
    if(POPUP.hasSlt()){ifr.className="hidden"}
    }
}

function addLoadEvent(func)
{
	var oldonload = window.onload;
	if(typeof window.onload != 'function')
	{
		window.onload = func;
	}
	else
	{
		window.onload = function(){oldonload();func();}
	}
}

addLoadEvent(POPUP.getH);

