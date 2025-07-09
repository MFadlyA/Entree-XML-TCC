const username = document.querySelector(".input.username");
const password = document.querySelector(".input.password");


function addcl(){
	let parent = this.parentNode.parentNode;
	parent.classList.add("focus");
}

function remcl(){
	let parent = this.parentNode.parentNode;
	if(this.value == ""){
		parent.classList.remove("focus");
	}
}

	username.addEventListener("focus", addcl);
	username.addEventListener("blur", remcl);

	password.addEventListener("focus", addcl);
	password.addEventListener("blur", remcl);
