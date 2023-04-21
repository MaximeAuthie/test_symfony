//? Récupérer la div dans laquelle on va afficher les articles
let container = document.querySelector('#container');
let url = 'https://127.0.0.1:8000/api/article/all'; // ou 'https://localhost/api/article/all' sur certaines machines
let url2 = 'https://127.0.0.1:8000/api/article/delete/';
let charge = false;

//On rédige notre script fetch
const getArticles = fetch(url)
    //On va regarder le statut et le message
    .then(async response => {
        //Vérification du code erreur serveur
        if (response.status == 500) {
            container.textContent = "Le serveur est en maintenance.";
        } else {
            //On récupère le json
            const data = await response.json();

            //Cas où tout va bien
            if (response.status == 200) {

                data.forEach(element => {
                    console.log(element);
                    const article = document.createElement('div');
                    article.setAttribute('class', element.id);
                    container.appendChild(article);
                    const titre = document.createElement('h2');
                    titre.innerText=element.titre;
                    const contenu = document.createElement('p');
                    contenu.innerText=element.contenu;
                    const date = document.createElement('p');
                    date.textContent = element.date.substring(0,10);
                    const icone = document.createElement('i');
                    icone.setAttribute('class','fa-solid fa-trash-can delete');
                    icone.setAttribute('id',element.id) // On met l'id de l'article en attribut du btn pour la récupérer dans l'api delete
                    article.appendChild(titre);
                    article.appendChild(contenu);
                    article.appendChild(date);
                    article.appendChild(icone);
                    icone.addEventListener('click', ()=> { //ici on ajoute un addEventListener pour déclancher la supression de l'article au click
                        fetch(url2+icone.id, {
                            method: 'DELETE'
                        })
                            .then(async responseSup =>{
                                //Vérification du code erreur du serveur
                                if (responseSup.status == 500) {
                                    //affichage de l'erreur
                                    alert('Le serveur est en maintenance');
                                } else {
                                    //récupérer le json de la supression
                                    const dataSup = await responseSup.json();
                                    if (responseSup.status == 200) {
                                        article.remove();
                                        alert(dataSup.erreur);
                                    }
                                    if (responseSup.status == 400) {
                                        const error = document.createElement('p');
                                        alert(dataSup.erreur); //erreur est la variable renvoyé dans le json en cas d'erreur
                                    }
                                }
                            })
                    })
                    charge = true; //on paasse cette variable à true pour déclancher l'exécution du script pour delete
                });
            }
            //Cas où il n'y a pas d'articles
            if (response.status == 206) {
                container.textContent = data.erreur; //erreur car c'est la propriété qui est renvoyée par l'api : "erreur : l'article existe déjà" => "erreur :" est la propriété
            }
        }
    })
    // .catch(error, () => {
    //     container.textContent = error;
    // })



    
