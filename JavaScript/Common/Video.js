/* DRL FIXIT! We need to store the delay in an attribute, and look for it in each iframe. If the attribute is there we
remove the src (and store it in another attribute?), then start a timer for the delay and when it goes off set the src
back to the original value.

var timer = 10000; //10sec.
var url = 'https://www.youtube.com/embed/Kc4WPd-sEG8?autoplay=1&rel=0&showinfo=0&autohide=1&color=white';

DocumentLoad.AddCallback(function() {
	var video = document.querySelector(".content_video");
	if (video){
		setTimeout(function() {
			video.querySelector("iframe").src = url;
		}, timer);
	}
});
 */
