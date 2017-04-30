function onDOMLoaded(){

    //add event listeners
    addEventListeners();

    //unhide homepage div (SPA)
    render(window.location.hash);

} //end onDOMLoaded


//****************************START EVENT LISTENERS************************************************
function addEventListeners(){

    //--------------------------------------------
    //Event listener for user registration (click)
    const btn_register = document.getElementById("btn_register");
    btn_register.addEventListener("click", (e)=>{
        e.preventDefault();

        //get details from form
        let fNameText = document.getElementById("fName").value;
        let lNameText = document.getElementById("lName").value;
        let emailText1 = document.getElementById("email1").value;
        let emailText2 = document.getElementById("email2").value;
        let streetText = document.getElementById("street").value;
        let cityText = document.getElementById("city").value;
        let postalText = document.getElementById("postal").value;
        let ageText = document.getElementById("age").value;
        
        if (emailText1=="" || fNameText=="" || lNameText==""){
            document.getElementById("span_register").innerHTML = "<span style='color:red'>You must complete the fields named 'First name', 'Last name' & 'Email address 1'.</span>";
            return;
        }
        
        //create array for emails
        let emailArray = (emailText2=="" ? [emailText1] : [emailText1, emailText2]);

        let payload = {
            fName: fNameText,
            lName: lNameText,
            email: emailArray,
            street: streetText,
            city: cityText,
            postal: postalText,
            age: ageText
        }

        //post user details to mongoDB via AJAX from INSERT command
        $.ajax({
            type: (e.target.innerHTML=="Submit" ? "POST" : "PUT"),
            url: "register.php",
            data: {dataKey: JSON.stringify(payload)},
            success:(data)=>{
                if(data !=""){
                    //notify user whether registration failed or passed
                    document.getElementById("span_register").innerHTML = data;
                    
                    //...if was success
                    if(data.includes('Congratulations')){
                       //update value of register button
                        btn_register.innerHTML = "Edit Registration Details";
                        //add delist button
                        document.getElementById('btn_delist').style.display="inline";
                        //update message on login page
                        document.getElementById("span_login").innerHTML = "You are logged in as " + emailArray[0];
                        //...and show logout button
                        document.getElementById('btn_logout').style.display="inline";
                        //..finally disable email1 field as user needs this as PK for MySQL
                        document.getElementById("email1").disabled = true;

                    } //end if
                }//end if(data !="")
            }
        });   
    });//end handler 
    //--------------------------------------------

    
    //--------------------------------------------
    //Event listener for user delist (click)
    const btn_delist = document.getElementById("btn_delist");
    btn_delist.addEventListener("click", (e)=>{
        e.preventDefault();

        //get email from email1 input field
        let emailText = document.getElementById("email1").value;
        let payload = {
            email: emailText
        }

        $.ajax({
            type: ("DELETE"),
            url: "register.php",
            data: {dataKey: JSON.stringify(payload)},
            success:(data)=>{
                if(data !=""){
                    if(data == "success"){
                        alert("You have successfully de-listed");
                        location.reload();
                    }else{
                        document.getElementById("span_register").innerHTML = data;
                    }
                }
            }
        }); //end ajax
    })
    //--------------------------------------------














    //--------------------------------------------
    //Event listener for user login (click)
    const btn_login = document.getElementById("btn_login");
    btn_login.addEventListener("click", (e)=>{
        e.preventDefault();

        //get email from uname-input
        let emailText = document.getElementById("input_email").value;
        let payload = {
            email: emailText
        }

        //post email to mongoDB/MySQL for FIND command
        $.ajax({
            type: "GET",
            url: "login.php",
            data: {dataKey: payload},
            success:(data)=>{
                if(data !=""){
                    //****first, we update span with message from server (success or failure)
                    document.getElementById("span_login").innerHTML = data;

                    //...then if login is successful....
                    if(data.includes('Congratulations')){
                        console.log('wtf');

                        //...show logout button
                        document.getElementById('btn_logout').style.display="inline";

                        //edit registration button & show delist button
                        btn_register.innerHTML = "Edit Registration Details";
                        document.getElementById('btn_delist').style.display="inline";

                        //...then use JSON object (passed by echo in script tag) to populate registration form
                        script_json = document.getElementById('jsonObject');
                        let obj = JSON.parse(script_json.innerHTML);

                        document.getElementById('fName').value = obj.first_name;
                        document.getElementById('lName').value = obj.last_name;
                        document.getElementById('email1').value = obj.email_addresses[0];
                        document.getElementById('email2').value = (obj.email_addresses[1] == undefined ? "" : obj.email_addresses[1]);
                        document.getElementById('street').value = obj.street_address;
                        document.getElementById('city').value = obj.city;
                        document.getElementById('postal').value = obj.postal_code;
                        document.getElementById('age').value = obj.age;

                        //..finally disable email1 field as user needs this as PK for MySQL
                        document.getElementById("email1").disabled = true;

                    };
                }
            }
        });
    })//end handler
    //--------------------------------------------



    //--------------------------------------------
    //Event listener for user logoff (click)
    const btn_logout = document.getElementById("btn_logout");
    btn_logout.addEventListener("click", (e)=>{
        e.preventDefault();

        //ajax logout.php to unset/delete session
        $.ajax({
            type: "GET",
            url: "logout.php",
            success:()=>{
                alert("You have successfully logged out");
                //refersh page
                location.reload();
            }
        })
    })
    //--------------------------------------------



    //------------------------------------
    //event listener for SPA navigation
    window.onhashchange = function(){
        // render function is called every hash change.
        render(window.location.hash);
    };
    //------------------------------------

}
//******************************END EVENT LISTENERS*****************************








//****************************SPA NAVIGATION***********************************************************
function render(hashKey) {

    //first hide all divs
    let pages = document.querySelectorAll(".page");
    for (let i = 0; i < pages.length; ++i) {
        pages[i].style.display = 'none';
    }

     //...now do same with lis
    let lis_nav = document.querySelectorAll(".navLi");
    for (let i = 0; i < lis_nav.length; ++i) {
        lis_nav[i].classList.remove("active");
    }

    //then unhide the one that user selected
    //console.log(hashKey);
    switch(hashKey){
        case "":
            pages[0].style.display = 'block';
            document.getElementById("li_home").classList.add("active");
            break;
        case "#home":
            pages[0].style.display = 'block';
            document.getElementById("li_home").classList.add("active");
            break;
        case "#register":
            pages[1].style.display = 'block';
            document.getElementById("li_register").classList.add("active");
            break;
        case "#about":
            pages[2].style.display = 'block';
            document.getElementById("li_about").classList.add("active");
            break;
        default:
            pages[0].style.display = 'block';
            document.getElementById("li_home").classList.add("active");
    }// end switch

} //end fn

//****************************END SPA NAVIGATION********************************************************






//**************************CHILD FNS****************************************************


//**************************END CHILD FNS************************************************






 
