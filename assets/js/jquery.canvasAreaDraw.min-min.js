!function(t){t.fn.canvasAreaDraw=function(t){this.each((function(n,a){e.apply(a,[n,a,t])}))};var e=function(e,a,o){var r,f,i,s,u,l,h,c,g,v,p,d,m,M,b;i=t.extend({imageUrl:t(this).attr("data-image-url")},o),r=t(this).val().length?t(this).val().split(",").map((function(t){return parseInt(t,10)})):[],s=t('<button type="button" class="btn"><i class="icon-trash"></i>Clear</button>'),u=t("<canvas>"),l=u[0].getContext("2d"),h=new Image,d=function(){u.attr("height",h.height).attr("width",h.width),c()},t(h).load(d),h.src=i.imageUrl,h.loaded&&d(),u.css({background:"url("+h.src+")"}),t(document).ready((function(){t(a).after("<br>",u,"<br>",s),s.click(m),u.on("mousedown",g),u.on("contextmenu",M),u.on("mouseup",v)})),m=function(){r=[],c()},p=function(e){e.offsetX||(e.offsetX=e.pageX-t(e.target).offset().left,e.offsetY=e.pageY-t(e.target).offset().top),r[f]=Math.round(e.offsetX),r[f+1]=Math.round(e.offsetY),c()},v=function(){t(this).off("mousemove"),b(),f=null},M=function(e){e.preventDefault(),e.offsetX||(e.offsetX=e.pageX-t(e.target).offset().left,e.offsetY=e.pageY-t(e.target).offset().top);for(var n=e.offsetX,a=e.offsetY,o=0;o<r.length;o+=2)if(dis=Math.sqrt(Math.pow(n-r[o],2)+Math.pow(a-r[o+1],2)),6>dis)return r.splice(o,2),c(),b(),!1;return!1},g=function(e){var a,o,i,s,u=r.length;if(3===e.which)return!1;e.preventDefault(),e.offsetX||(e.offsetX=e.pageX-t(e.target).offset().left,e.offsetY=e.pageY-t(e.target).offset().top),a=e.offsetX,o=e.offsetY;for(var l=0;l<r.length;l+=2)if(6>(i=Math.sqrt(Math.pow(a-r[l],2)+Math.pow(o-r[l+1],2))))return f=l,t(this).on("mousemove",p),!1;for(var l=0;l<r.length;l+=2)l>1&&(6>(s=n(a,o,r[l],r[l+1],r[l-2],r[l-1],!0))&&(u=l));return r.splice(u,0,Math.round(a),Math.round(o)),f=u,t(this).on("mousemove",p),c(),b(),!1},c=function(){if(l.canvas.width=l.canvas.width,b(),r.length<2)return!1;l.globalCompositeOperation="destination-over",l.fillStyle="rgb(255,255,255)",l.strokeStyle="rgb(255,20,20)",l.lineWidth=1,l.beginPath(),l.moveTo(r[0],r[1]);for(var t=0;t<r.length;t+=2)l.fillRect(r[t]-2,r[t+1]-2,4,4),l.strokeRect(r[t]-2,r[t+1]-2,4,4),r.length>2&&t>1&&l.lineTo(r[t],r[t+1]);l.closePath(),l.fillStyle="rgba(255,0,0,0.3)",l.fill(),l.stroke()},b=function(){t(a).val(r.join(","))},t(a).on("change",(function(){r=t(a).val(),c()}))};t(document).ready((function(){t(".canvas-area[data-image-url]").canvasAreaDraw()}));var n=function(t,e,n,a,o,r,f){function i(t,e,n,a){return Math.sqrt((t-=n)*t+(e-=a)*e)}if(!f||(f=function(t,e,n,a,o,r){if(!(o-n))return{x:n,y:e};if(!(r-a))return{x:t,y:a};var f,i=-1/((r-a)/(o-n));return{x:f=(o*(t*i-e+a)+n*(t*-i+e-r))/(i*(o-n)+a-r),y:i*f-i*t+e}}(t,e,n,a,o,r)).x>=Math.min(n,o)&&f.x<=Math.max(n,o)&&f.y>=Math.min(a,r)&&f.y<=Math.max(a,r)){var s=a-r,u=o-n,l=n*r-a*o;return Math.abs(s*t+u*e+l)/Math.sqrt(s*s+u*u)}var h=i(t,e,n,a),c=i(t,e,o,r);return h>c?c:h}}(jQuery);