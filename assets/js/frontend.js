/**
 * Frontend JS - Filters & Pagination
 * Minified for speed
 */
(function(){
'use strict';

// Apply settings as CSS variables
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

// Filter handling
document.addEventListener('DOMContentLoaded',function(){
var filters=document.querySelectorAll('.tkmtb-filter');
var filterBtn=document.querySelector('.tkmtb-filter-btn');
var resetBtn=document.querySelector('.tkmtb-reset-btn');

if(filterBtn){
filterBtn.addEventListener('click',function(){
var params=new URLSearchParams(window.location.search);
filters.forEach(function(f){
if(f.value){
params.set(f.name,f.value);
}else{
params.delete(f.name);
}
});
params.delete('tpage');
window.location.search=params.toString();
});
}

if(resetBtn){
resetBtn.addEventListener('click',function(){
var url=window.location.href.split('?')[0];
window.location.href=url;
});
}

// Enter key on filters
filters.forEach(function(f){
f.addEventListener('keypress',function(e){
if(e.key==='Enter'&&filterBtn){
filterBtn.click();
}
});
});

// Load more
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
