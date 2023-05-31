let articles = document.querySelector('#articles');
let error = document.querySelector('#error');

const urlArticles = 'https://127.0.0.1:8000/api/articles/get/all';

const headers = {Authorization: 'Bearer ' + localStorage.getItem("jwt")};
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
                            const liste = await response.json();
                            liste.forEach(element => {
                                const article = document.createElement('div');
                                article.style.border = 'solid 1px black';
                                article.style.textAlign = 'center';
                                const titre = document.createElement('h3');
                                titre.textContent = element.titre;
                                const content = document.createElement('textarea');
                                content.textContent = element.contenu;
                                const date = document.createElement('p');
                                date.textContent = element.date;
                                articles.appendChild(article);
                                article.appendChild(titre);
                                article.appendChild(content);
                                article.appendChild(date);
                            });

                        } else {
                            const jsonError = await response.json();
                            error.textContent = jsonError.Erreur;
                        }
                    });
                      
            