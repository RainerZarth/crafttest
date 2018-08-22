$(document).ready(function(){
    $("#los").click(function(){

    var coffee = $("input[name='group']");
	var id = "";
	var i;

    /*for (i = 0; i < coffee.length; i++) {
        if (coffee[i].checked) {
            id = coffee[i].value;
        }
    }*/
    document.getElementById("output").innerHTML = id;
    });
});