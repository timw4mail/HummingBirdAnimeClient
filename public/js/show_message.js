function add_message(type, message)
{
	var template = '<div class="message ' + type + '"><span class="icon"></span>' + message + '<span class="close" onclick="this.parentElement.style.display=\'none\'">x</span></div>';

	if ($(".message").length > 0)
	{
		$(".message").replaceWith(template);
	}
	else
	{
		$("main").prepend(template);
	}
}