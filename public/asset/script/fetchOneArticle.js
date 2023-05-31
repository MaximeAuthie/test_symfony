let articles = document.querySelector('#articles');
let error = document.querySelector('#error');
const urlcourante = document.location.href;
const id = urlcourante.substring(urlcourante.lastIndexOf("/")+1);

const urlArticles = 'https://127.0.0.1:8000/api/articles/get/' + id;

const headers = {Authorization: 'Bearer ' + localStorage.getItem("jwt")};
console.log(headers);
const token = fetch(urlArticles, {
                        method: 'GET',
                        headers,
                    })
                    .then(async response => {
                        if (response.status == 400) {
                            const expired = await response.json();
                            if (expired.Erreur == 'Expired token') {
                                location.href = 'https://127.0.0.1:8000/api/localToken';
                            } else {
                                error.textContent = expired.Erreur;
                            }
                        }
                        if (response.status == 200 ) {
                            const article = await response.json();
                            const articleDiv = document.createElement('div');
                            articleDiv.style.border = 'solid 1px black';
                            articleDiv.style.textAlign = 'center';
                            const titre = document.createElement('h3');
                            titre.textContent = article.titre;
                            const content = document.createElement('textarea');
                            content.textContent = article.contenu;
                            const date = document.createElement('p');
                            date.textContent = article.date;
                            articles.appendChild(articleDiv);
                            articleDiv.appendChild(titre);
                            articleDiv.appendChild(content);
                            articleDiv.appendChild(date);
                        } else {
                            const jsonError = await response.json();
                            error.textContent = jsonError.Erreur;
                        }
                    });