/**
 * Frontend JS - Pagination and Grid Color Generation
 */
(function(){
'use strict';

// Apply settings as CSS variables for theme customization
if(typeof tkmtbData!=='undefined'&&tkmtbData.settings){
var s=tkmtbData.settings;
var root=document.documentElement;
if(s.button_bg)root.style.setProperty('--tkmtb-button-bg',s.button_bg);
if(s.button_bg_hover)root.style.setProperty('--tkmtb-button-hover',s.button_bg_hover);
if(s.button_font_color)root.style.setProperty('--tkmtb-button-font',s.button_font_color);
if(s.bg_header)root.style.setProperty('--tkmtb-bg-header',s.bg_header);
if(s.bg_cell)root.style.setProperty('--tkmtb-bg-cell',s.bg_cell);
if(s.bg_cell_hover)root.style.setProperty('--tkmtb-bg-hover',s.bg_cell_hover);
}

// Generate unique colors for grid headers
function stringToColor(str){
var hash=0;
for(var i=0;i<str.length;i++){
hash=str.charCodeAt(i)+((hash<<5)-hash);
}
var h=hash%360;
return'hsl('+h+',65%,50%)';
}

// Apply colors to grid headers
document.addEventListener('DOMContentLoaded',function(){
var gridHeaders=document.querySelectorAll('.tkmtb-grid-header');
gridHeaders.forEach(function(header){
var title=header.getAttribute('data-title');
if(title){
var color=stringToColor(title);
header.style.background='linear-gradient(135deg,'+color+','+color+'dd)';
}
});

// Load more button (if pagination type is load_more)
var loadMoreBtn=document.querySelector('.tkmtb-load-more');
if(loadMoreBtn){
loadMoreBtn.addEventListener('click',function(){
var nextPage=parseInt(this.getAttribute('data-page'));
var params=new URLSearchParams(window.location.search);
params.set('tpage',nextPage);
window.location.search=params.toString();
});
}
});

})();
