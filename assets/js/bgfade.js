    function easeInOut(minValue,maxValue,totalSteps,actualStep,powr)
    {
	var delta = maxValue - minValue;
	var stepp = minValue+(Math.pow(((1 / totalSteps)*actualStep),powr)*delta);
	return Math.ceil(stepp)
    }

    function doBGFade(elem,startRGB,endRGB,finalColor,steps,intervals,powr)
    {
	if (elem.bgFadeInt) window.clearInterval(elem.bgFadeInt);
	var actStep = 0;
	elem.bgFadeInt = window.setInterval(
	    function() {
		    elem.css("backgroundColor", "rgb("+
			    easeInOut(startRGB[0],endRGB[0],steps,actStep,powr)+","+
			    easeInOut(startRGB[1],endRGB[1],steps,actStep,powr)+","+
			    easeInOut(startRGB[2],endRGB[2],steps,actStep,powr)+")"
		    );
		    actStep++;
		    if (actStep > steps) {
		    elem.css("backgroundColor", finalColor);
		    window.clearInterval(elem.bgFadeInt);
		    }
	    }
	    ,intervals)
    }
