-- Remplace entierement le contenu de la table project.
-- Genere automatiquement, a executer dans l'onglet SQL de phpMyAdmin (base heplmecommunity).

DELETE FROM project;

INSERT INTO project (title, description, slug, image, data) VALUES ('Construction de puits', 'Notre association a déjà construit environ 2000 puits à travers le monde, apportant de l''eau potable aux communautés dans le besoin. Les projets ont eu lieu dans plusieurs pays : Maroc, Sénégal, Népal et Bangladesh.

Maroc : Dans certaines régions désertiques, les habitants n''avaient aucun accès local à l''eau potable. Avant notre intervention, ils devaient parcourir de longues distances dans des conditions difficiles pour trouver de l''eau, ce qui limitait leur temps pour l''éducation et les activités économiques.

Sénégal : Dans les villages ruraux, les habitants devaient souvent marcher jusqu''à 8 kilomètres par jour pour accéder à une source d''eau potable. Cette corvée quotidienne pesait particulièrement sur les enfants et les femmes, les empêchant de se concentrer sur l''école ou le travail.

Népal : La topographie montagneuse rend l''accès à l''eau très difficile. Beaucoup de villages étaient isolés, et l''eau potable n''était disponible qu''au prix de longs trajets dangereux dans la montagne.

Bangladesh : Dans certaines zones, l''eau disponible était contaminée et impropre à la consommation. Les habitants devaient chercher des sources sûres parfois très éloignées, augmentant les risques sanitaires et limitant les activités quotidiennes.

Grâce à vos dons, nous avons pu construire des puits directement dans les villages, réduisant drastiquement les distances parcourues et améliorant la santé, la sécurité et le bien-être des habitants.

Les coûts des projets varient selon le pays, en fonction des matériaux, de la main-d''œuvre et des spécificités locales. Pour vous donner une idée, le prix d''un puits commence à 200€ au Népal et peut aller jusqu''à 2500€ au Maroc.

Chaque don contribue directement à fournir de l''eau saine et à améliorer durablement la vie des communautés. En soutenant nos projets, vous participez à un changement concret et durable, permettant aux enfants d''aller à l''école, aux familles de se consacrer à leur travail, et à l''ensemble de la communauté de bénéficier d''une vie plus sûre et plus saine.', 'puits', 'https://images.unsplash.com/photo-1526599256864-6bedb9d7dfb5?q=80&w=1171', '[{"country": "Maroc", "count": 40, "lat": 31.7917, "lng": -7.0926}, {"country": "Sénégal", "count": 20, "lat": 14.4974, "lng": -14.4524}, {"country": "Népal", "count": 1000, "lat": 28.3949, "lng": 84.124}, {"country": "Bangladesh", "count": 500, "lat": 23.685, "lng": 90.3563}]');

INSERT INTO project (title, description, slug, image, data) VALUES ('Dons de vêtements', 'Après le séisme qui a frappé le Maroc, nous intervenons dans plusieurs régions touchées, en particulier à Telouet, où nous continuons aujourd''hui encore à soutenir les habitants. Nous y distribuons des vêtements, des couvertures et des équipements de première nécessité pour aider les familles à se reconstruire et à affronter les conditions climatiques difficiles de cette zone montagneuse du Haut Atlas, où les hivers sont rudes.

Grâce à un partenariat avec Decathlon, nous recevons régulièrement des équipements et vêtements qui viennent renforcer nos actions sur place. Les envois restent toutefois coûteux et logistiquement complexes, mais ils permettent de toucher un maximum de familles dans le besoin.

Chaque don contribue directement à apporter chaleur, dignité et protection aux habitants de Telouet. Votre soutien nous permet de poursuivre ces distributions et d''envoyer du matériel là où il est le plus nécessaire.', 'vetements', 'https://images.unsplash.com/photo-1743800531573-2b1c442d3885?q=80&w=1170', '[{"country": "Telouet, Maroc", "count": 4000, "lat": 31.2667, "lng": -7.2833}]');

INSERT INTO project (title, description, slug, image, data) VALUES ('Distribution de nourriture à Calais (migrants)', 'Nos distributions alimentaires à Calais visent à soutenir les migrants et réfugiés vivant dans des conditions extrêmement précaires aux abords de la ville. Nos bénévoles distribuent régulièrement des repas chauds et des produits de première nécessité aux personnes en transit, souvent démunies de tout après un parcours migratoire long et dangereux.

Ces populations sont particulièrement vulnérables : exposées aux intempéries et régulièrement déplacées par les autorités locales qui confisquent leurs affaires, elles dépendent largement du soutien associatif pour se nourrir au quotidien.

Grâce à vos dons, nous pouvons maintenir une présence régulière sur le terrain et garantir un accès à une alimentation basique à des dizaines de personnes chaque jour. Chaque contribution permet de financer l''achat de denrées, le matériel de distribution et la logistique nécessaire pour intervenir dans la durée.', 'nourriture-calais', 'https://plus.unsplash.com/premium_photo-1683141173692-aba4763bce41?q=80&w=1170', '[{"country": "Calais, France", "count": 300, "lat": 50.9513, "lng": 1.8587}]');
