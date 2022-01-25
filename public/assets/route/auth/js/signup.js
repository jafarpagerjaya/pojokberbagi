$(function() {	
	$('input[type="password"]#password').passy(function( strength, valid ) {
		let value = 0,
			textClassName;
		if (strength < 1) {
			value = "Bad";
			textClassName = "text-danger";
		} else if (strength == 1) {
			value = "Weak";
			textClassName = "text-warning";
		} else if (strength == 2) {
			value = "Good"
			textClassName = "text-purple";
		} else {
			value = "Strong"
			textClassName = "text-success";
		}
		let el = $("form").find('#password-strength b');
			el.text(value).removeClass().addClass(textClassName);
	});
	
	$('input[type="password"]#password').on('keyup', function() {
		if ($(this).val().length) {
			$("#password-strength").removeClass('d-none');
		} else {
			$("#password-strength").addClass('d-none');
		}
	});
});