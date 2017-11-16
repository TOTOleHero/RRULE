function equalHeights(selector){
	var items = document.querySelectorAll(selector);
	if(!items){
		return false;
	}	
	equalHeightsReset(selector);
	var max = 0;
	for(var i = 0; i < items.length; i++){
	
		if(max < items[i].offsetHeight){
			max = items[i].offsetHeight;
		}
	}
	
	for(var i = 0; i < items.length; i++){
		items[i].style.height = max;
	}	
}

function equalHeightsReset(selector){
	var items = document.querySelectorAll(selector);
	for(var i = 0; i < items.length; i++){
		items[i].style.removeProperty('height');
		}
}

DocumentLoad.AddCallback(function() {
   if( window.innerWidth > 640){
      equalHeights('.columns li');
   }
   else{
      equalHeightsReset('.columns li');
   }
   
   setTimeout(function() {
      equalHeights('.features li');
   }, 700);
});

window.addEventListener("resize", function(){
   if( window.innerWidth > 640){
      equalHeights('.columns li');
   }
   else{
      equalHeightsReset('.columns li');
   }
   
   equalHeights('.features li');
});