/**
 * Frontend JS - Live Search, Pagination, and Modern Features
 * Optimized for performance with debouncing
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

// Debounce function for search performance
function debounce(func,wait){
var timeout;
return function(){
var context=this,args=arguments;
clearTimeout(timeout);
timeout=setTimeout(function(){func.apply(context,args);},wait);
};
}

// Highlight search term in text
function highlightText(text,term){
if(!term)return text;
var regex=new RegExp('('+term.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi');
return text.replace(regex,'<span class="tkmtb-highlight">$1</span>');
}

// Remove highlights
function removeHighlights(element){
var highlights=element.querySelectorAll('.tkmtb-highlight');
highlights.forEach(function(el){
var text=document.createTextNode(el.textContent);
el.parentNode.replaceChild(text,el);
});
}

// Live Search Function
function initLiveSearch(){
var searchInput=document.querySelector('.tkmtb-search');
if(!searchInput)return;

var searchableRows=document.querySelectorAll('[data-searchable]');
var countEl=document.querySelector('.tkmtb-search-count');
var tableWrapper=document.querySelector('.tkmtb-table');
var cardsWrapper=document.querySelector('.tkmtb-cards');

var performSearch=function(){
var term=searchInput.value.trim().toLowerCase();
var visibleCount=0;

// Remove existing highlights
searchableRows.forEach(function(row){
removeHighlights(row);
});

searchableRows.forEach(function(row){
var text=row.textContent.toLowerCase();
var isVisible=!term||text.indexOf(term)!==-1;

if(isVisible){
row.style.display='';
visibleCount++;

// Add highlights if search term exists
if(term){
var cells=row.querySelectorAll('td,h3,div');
cells.forEach(function(cell){
if(cell.textContent.toLowerCase().indexOf(term)!==-1){
cell.innerHTML=highlightText(cell.innerHTML,term);
}
});
}
}else{
row.style.display='none';
}
});

// Update count
if(countEl){
if(term){
countEl.textContent=visibleCount+' result'+(visibleCount!==1?'s':'')+' found';
countEl.style.display='block';
}else{
countEl.style.display='none';
}
}

// Show "no results" message if needed
var noResultsEl=document.querySelector('.tkmtb-no-results');
if(visibleCount===0&&term){
if(!noResultsEl){
noResultsEl=document.createElement('div');
noResultsEl.className='tkmtb-no-results';
noResultsEl.textContent='No documents found matching "'+term+'"';
if(tableWrapper){
tableWrapper.parentNode.insertBefore(noResultsEl,tableWrapper.nextSibling);
}else if(cardsWrapper){
cardsWrapper.appendChild(noResultsEl);
}
}else{
noResultsEl.style.display='block';
}
}else if(noResultsEl){
noResultsEl.style.display='none';
}
};

searchInput.addEventListener('input',debounce(performSearch,300));

// Clear search on Escape
searchInput.addEventListener('keydown',function(e){
if(e.key==='Escape'){
searchInput.value='';
performSearch();
searchInput.blur();
}
});
}

// Pagination handling
document.addEventListener('DOMContentLoaded',function(){
// Initialize live search
initLiveSearch();

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
