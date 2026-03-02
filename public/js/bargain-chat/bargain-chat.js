var chat_data = {};	
firebase.auth().onAuthStateChanged(function(user) {
	if (user) {
	    user_uuid = user.uid;
		//getUsers();		   
	}else{
	    console.log("Not sign in");
	}
});
