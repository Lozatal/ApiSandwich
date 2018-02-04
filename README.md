# API - Le Bon Sandwich

/sql/bdd.sql ==> contient le fichier sql permettant de créer la base de données, les tables et quelques données.

## Routes

### Catégories

Accéder à la liste des catégories: (via un get)
>categories

Accéder à une catégorie: (via un get)
>categories/{id}

Créer une catégorie: (via un post)
>categories

Modifier une catégorie: (via un put)
>categories/{id}

### Sandwichs

Accéder à la liste des sandwichs: (via un get)
>sandwichs

Accéder à un sandwich: (via un get)
>sandwichs/{id}

Paramètres possibles:
>sandwichs?type=&img=&page=&size=
