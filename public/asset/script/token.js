let error = document.querySelector('#error');

let mail = prompt('Saisir votre mail');
let password = prompt('Saisir votre mot de passe');

const url = 'https://127.0.0.1:8000/api/identification';
let json = JSON.stringify({email: mail, password: password});

//Stockage du token jwt dans le local storage
const token = fetch( url , {
                    method: 'POST',
                    body: json
                })
                .then( async response => {
                    if (response.status == 200) {
                        let jwt = await response.json();
                        localStorage.setItem("jwt",jwt);
                    } else {
                        const error = await response.json();
                        error.textContent = error.Erreur;
                    }
                }
               )