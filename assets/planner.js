var highlightColor = "#95D662";
var disabledColor = "#eeeeee";

function addClass(sem, pid, n)
{
	var cid = Math.floor(Math.random() * 1000000000);

	var newClass = "<div id='"+ cid +"'><div class='remove' id='"+ cid +"r' rel='tooltip' data-trigger='hover' data-placement='top' data-animation='true' data-original-title='Delete class' onclick='removeClass("+ cid +",\""+ n +"\")'></div><input class='class_complete' id='"+ cid +"cb' rel='tooltip' data-trigger='hover' data-placement='left' data-animation='true' data-original-title='Toggle completion' onclick='comp("+ cid +",\""+ n +"\")' style='margin-right:4px;' type='checkbox' ><input class='class_input' id='"+ cid +"n' rel='tooltip' data-trigger='focus' data-placement='top' data-animation='true' data-original-title='Enter class name' type='text' value='' disabled='disabled'><input class='class_credits' style='margin-left:4px;' id='"+ cid +"c' rel='tooltip' data-trigger='focus' data-placement='top' data-animation='true' data-original-title='Enter class credits' data-credits='0' type='text' value='' maxlength='3' disabled='disabled'><div class='save' id='"+ cid +"s' rel='tooltip' data-trigger='hover' data-placement='top' data-animation='true' data-original-title='Save changes' onclick='save("+ sem +", "+ cid +", \""+ pid +"\", \""+ n +"\", true)'></div><div class='edit' id='"+ cid +"e' rel='tooltip' data-trigger='hover' data-placement='top' data-animation='true' data-original-title='Edit class' onclick='edit("+ cid +")'></div></div>";

	$("#"+sem).append(newClass);
	refreshListeners();
}

function removeClass(cid, n)
{
	var theClass = "#"+cid;
	var creds = "#"+cid+"c";
	var cb = "#"+cid+"cb";

	$(theClass).hide("fast");

	$.ajax({
			url: "process.php?a=r",
			type: "POST",
			data: {
				classid: cid,
				non: n
			}
		});

	if($(creds).val() != "" && $(cb).is(':checked'))
	{
		currentCredits = parseFloat(currentCredits)-parseFloat($(creds).val());
		updateProgress(currentCredits, maxCredits);
	}
}

function completeAll(sem,n)
{
	for (var i = 0; i < fullSchedule.length; i++)
	{
		if(fullSchedule[i].semester == sem && fullSchedule[i].completed == 0)
		{
			//$("#"+fullSchedule[i].id+"cb").click();
			document.getElementById(fullSchedule[i].id+"cb").click();
			fullSchedule[i].completed = 1;

			/*comp(fullSchedule[i].id, "\""+ n +"\"");
			alert(fullSchedule[i].id+"\""+ n +"\"");*/
		}
	}
}

function edit(cid)
{
	var edit = "#"+cid+"e";
	var save = "#"+cid+"s";
	var name = "#"+cid+"n";
	var creds = "#"+cid+"c";
	var remove = "#"+cid+"r";

	$(edit).hide();
	$(save).show();
	$(name).attr("disabled", false);
	$(name).focus();
	$(creds).attr("disabled", false);
	$(remove).show();

	
	$(name).css("backgroundColor", "#ffffff");
	$(creds).css("backgroundColor", "#ffffff");
	
}

function save(sem,cid, pid, n, justAdded)
{
	justAdded = typeof justAdded !== 'undefined' ? justAdded : false;

	var edit = "#"+cid+"e";
	var save = "#"+cid+"s";
	var name = "#"+cid+"n";
	var creds = "#"+cid+"c";
	var remove = "#"+cid+"r";
	var cb = "#"+cid+"cb";

	$(edit).show();
	$(save).hide();
	$(name).attr("disabled", true);
	$(creds).attr("disabled", true);
	$(remove).hide();

	var newName = $(name).val();
	var newCreds = $(creds).val();
	var newComp;

	if($(cb).is(':checked'))
		var newComp = 1;
	else
		var newComp = 0;

	if(!$(cb).is(':checked'))
	{
		$(name).css("backgroundColor", disabledColor);
		$(creds).css("backgroundColor", disabledColor);
	}
	else
	{
		$(name).css("backgroundColor", highlightColor);
		$(creds).css("backgroundColor", highlightColor);

		//Update progress bar with new credit values immediately
		var oldCreds = $(creds).data("credits");
		if(newCreds != oldCreds)
		{	
			currentCredits = parseFloat(currentCredits) - parseFloat(oldCreds);
			currentCredits = parseFloat(currentCredits) + parseFloat(newCreds);
			$(creds).data("credits", newCreds);

			updateProgress(currentCredits, maxCredits);
		}
	}

	$.ajax({
			url: "process.php?a=s",
			type: "POST",
			data: {
				semester: sem,
				planid: pid,
				classid: cid,
				name: newName,
				creds: newCreds,
				comp: newComp,
				non: n
			}
		});
}

function comp(cid, n)
{
	var name = "#"+cid+"n";
	var creds = "#"+cid+"c";
	var cb = "#"+cid+"cb";

	var creditValue = $(creds).val();
	if(isNaN(parseFloat(creditValue)))
	{
		creditValue = parseFloat(0);
	}

	if(!$(cb).is(':checked'))
	{
		if($(name).attr("disabled") == false || $(creds).attr("disabled") == null)
		{
			$(name).css("backgroundColor", "#ffffff");
			$(creds).css("backgroundColor", "#ffffff");
		}
		else
		{
			$(name).css("backgroundColor", disabledColor);
			$(creds).css("backgroundColor", disabledColor);
		}

		$.ajax({
				url: "process.php?a=c",
				type: "POST",
				data: {
					comp: "0",
					classid: cid,
					non: n
				}
			});

		currentCredits = parseFloat(currentCredits) - parseFloat(creditValue);
		updateProgress(currentCredits, maxCredits);

		for (var i = 0; i < fullSchedule.length; i++)
		{
			if(fullSchedule[i].id == cid)
			{
				fullSchedule[i].completed = 0;
			}
		}
	}
	else
	{
		$(name).css("backgroundColor", highlightColor);
		$(creds).css("backgroundColor", highlightColor);

		$.ajax({
				url: "process.php?a=c",
				type: "POST",
				data: {
					comp: "1",
					classid: cid,
					non: n
				}
			});
		currentCredits = parseFloat(currentCredits) + parseFloat(creditValue);
		updateProgress(currentCredits, maxCredits);

		for (var i = 0; i < fullSchedule.length; i++)
		{
			if(fullSchedule[i].id == cid)
			{
				fullSchedule[i].completed = 1;
			}
		}
	}
}

function updateProgress(curCred, maxCred)
{
	var percen = parseFloat((curCred/maxCred)*100);

	$("#degreeProgress").css("width", percen+"%");

	$("#currentCredits").html(curCred+" ");
	$("#maxCredits").html(maxCred);
}

function login(n)
{
	$.ajax({
		  url: "process.php?a=l",
		  type: "POST",
		  data: {
					pwd: $("#planPwd").val(),
					non: n
				},
		  success: function(data) {
		    if(data == "0")
		    {
		    	$("#pwdError").html("Yo dawg your password's wrong fo' shizzle mah nizzle hizzle wizzle nah mean?");
		    	$("#pwdError").css("display", "block");
		    }
		    else
		    {
		    	location.reload();
		    }
		  }
		});
}

function setPassword(n)
{
	$.ajax({
	  url: "process.php?a=sp",
	  type: "POST",
	  data: {
				pwd: $("#setplanPwd").val(),
				non: n
			},
	  success: function(data) {
	    if(data == "0")
	    {
	    	$("#pwdStatus").html("Password change failed!");
	    	$("#pwdStatuspwdStatus").attr("class", "alert alert-error");
	    	$("#pwdStatuspwdStatus").css("display", "block");
	    }
	    else
	    {
	    	$("#pwdStatus").html("Password change successful!");
	    	$("#pwdStatus").attr("class", "alert alert-success");
	    	$("#pwdStatus").css("display", "block");
	    }
	  }
	});
}

function removePassword(n)
{
	$.ajax({
	  url: "process.php?a=rp",
	  type: "POST",
	  data: {
				non: n
			},
	  success: function(data) {
	    if(data == "0")
	    {
	    	$("#toast").html("Password removal failed!");
	    	$("#toast").attr("class", "alert alert-error");
	    	$("#toast").css("display", "block");
	    }
	    else
	    {
	    	$("#toast").html("Password successfully removed!");
	    	$("#toast").attr("class", "alert alert-success");
	    	$("#toast").css("display", "block");
	    }
	  }
	});
}

function refreshListeners()
{
	$(".class_complete").hover(function() {
        $(this).tooltip("show");
	  });
	$(".class_input").focus(function() {
		$(this).tooltip({ show: 0, hide: 500 });
	 //   $(this).tooltip("show");
	 //   $(this).delay(5000).tooltip("hide");
	  });
	$(".class_credits").focus(function() {
		$(this).tooltip({ show: 0, hide: 500 });
	//    $(this).tooltip("show");
	 //   $(this).delay(5000).tooltip("hide");
	  });
    $(".edit").hover(function() {
	    $(this).tooltip("show");
	  });
    $(".save").hover(function() {
	    $(this).tooltip("show");
	  });
	$(".remove").hover(function() {
	    $(this).tooltip("show");
	  });

	jQuery('.class_credits').keyup(function () { 
          this.value = this.value.replace(/[^0-9\.]/g,'');
      });
}