<?php
// src/DataFixtures/ProjectFixtures.php
namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $projects = [
            [
                'title' => 'Construction de puits',
                'slug' => 'puits',
                'description' => 'Notre association a déjà construit environ 2000 puits à travers le monde, apportant de l’eau potable aux communautés dans le besoin.
                    Les projets ont eu lieu dans plusieurs pays: Maroc, Sénégal, Népal et Bangladesh.

                    Maroc : Dans certaines régions désertiques, les habitants n’avaient aucun accès local à l’eau potable. Avant notre intervention, ils devaient parcourir de longues distances dans des conditions difficiles pour trouver de l’eau, ce qui limitait leur temps pour l’éducation et les activités économiques.

                    Sénégal : Dans les villages ruraux, les habitants devaient souvent marcher jusqu’à 8 kilomètres par jour pour accéder à une source d’eau potable. Cette corvée quotidienne pesait particulièrement sur les enfants et les femmes, les empêchant de se concentrer sur l’école ou le travail.

                    Népal : La topographie montagneuse rend l’accès à l’eau très difficile. Beaucoup de villages étaient isolés, et l’eau potable n’était disponible qu’au prix de longs trajets dangereux dans la montagne.

                    Bangladesh : Dans certaines zones, l’eau disponible était contaminée et impropre à la consommation. Les habitants devaient chercher des sources sûres parfois très éloignées, augmentant les risques sanitaires et limitant les activités quotidiennes.

                    Grâce à vos dons, nous avons pu construire des puits directement dans les villages, réduisant drastiquement les distances parcourues et améliorant la santé, la sécurité et le bien-être des habitants.

                    Les coûts des projets varient selon le pays, en fonction des matériaux, de la main-d’œuvre et des spécificités locales. Pour vous donner une idée, le prix d’un puits commence à 200€ au Népal et peut aller jusqu’à 2500€ au Maroc.

                    Chaque don contribue directement à fournir de l’eau saine et à améliorer durablement la vie des communautés. En soutenant nos projets, vous participez à un changement concret et durable, permettant aux enfants d’aller à l’école, aux familles de se consacrer à leur travail, et à l’ensemble de la communauté de bénéficier d’une vie plus sûre et plus saine.',
                'image' => 'https://images.unsplash.com/photo-1526599256864-6bedb9d7dfb5?q=80&w=1171',
                'data' => [
                    ['country' => 'Maroc', 'count' => 40, 'lat' => 31.7917, 'lng' => -7.0926],
                    ['country' => 'Sénégal', 'count' => 20, 'lat' => 14.4974, 'lng' => -14.4524],
                    ['country' => 'Népal', 'count' => 1000, 'lat' => 28.3949, 'lng' => 84.1240],
                    ['country' => 'Bangladesh', 'count' => 500, 'lat' => 23.6850, 'lng' => 90.3563],
                ]
            ],
            [
                'title' => 'Dons alimentaires',
                'slug' => 'alimentaire',
                'description' => 'Nos actions de dons alimentaires ont pour objectif de soutenir les familles et les personnes en difficulté en leur fournissant des aliments essentiels, sains et nutritifs.
                    Nous sommes intervenus dans plusieurs régions et pays pour apporter un soutien concret là où les besoins étaient les plus urgents.

                    France : À Paris, nous avons distribué des denrées alimentaires à des familles en grande précarité. À Calais, nous avons soutenu les migrants et réfugiés vivant dans des conditions difficiles, en leur fournissant des repas et des produits de première nécessité. Dans la région parisienne, nous avons travaillé avec plusieurs foyers sociaux pour garantir que les familles bénéficient régulièrement d’une aide alimentaire adaptée.

                    Maroc : Dans certaines villes et villages, de nombreuses familles n’ont pas un accès suffisant à une alimentation équilibrée. Nous avons mis en place des distributions régulières pour aider les enfants et les personnes âgées.

                    Sénégal : Dans les villages et zones rurales, nous avons apporté des denrées alimentaires aux familles les plus vulnérables, réduisant ainsi l’insécurité alimentaire et contribuant à une meilleure santé globale.

                    Grâce à vos dons, nous pouvons continuer ces actions et répondre aux besoins essentiels des communautés, en garantissant que chacun puisse accéder à des repas nutritifs et suffisants. Chaque contribution permet de nourrir des familles et d’offrir un peu de réconfort et de sécurité alimentaire aux plus démunis.',
                'image' => 'https://plus.unsplash.com/premium_photo-1683141173692-aba4763bce41?q=80&w=1170',
                'data' => [
                    ['country' => 'Maroc', 'count' => 40, 'lat' => 31.7917, 'lng' => -7.0926],
                    ['country' => 'Sénégal', 'count' => 20, 'lat' => 14.4974, 'lng' => -14.4524],
                    ['country' => 'France', 'count' => 10000, 'lat' => 46.2276, 'lng' => 2.2137],
                ]
            ],
            [
                'title' => 'Dons de vêtements',
                'slug' => 'vetements',
                'description' => 'Nos actions de dons de vêtements visent à réchauffer le cœur et le quotidien des personnes en difficulté, qu’il s’agisse de catastrophes naturelles ou de précarité quotidienne.

                Maroc : Après le séisme, nous sommes intervenus dans plusieurs régions touchées, en particulier Telouet, où nous continuons encore aujourd’hui à soutenir les habitants. Nous distribuons vêtements, couvertures et équipements nécessaires pour aider les familles à se reconstruire et affronter les conditions difficiles.

                France – Calais : Dans cette zone où vivent de nombreux migrants, nos distributions de vêtements sont essentielles pour protéger ces personnes vulnérables contre le froid et les intempéries. Malheureusement, ces populations sont régulièrement ciblées par les autorités locales, qui confisquent souvent leurs affaires, ce qui rend notre soutien encore plus crucial et urgent.

                Partenariat : Grâce à Decathlon, un partenaire extraordinaire, nous recevons de nombreux équipements et vêtements pour renforcer nos actions. Cependant, les envois restent souvent coûteux et logistiques complexes, mais ils permettent de toucher le maximum de personnes dans le besoin.

                Chaque don contribue directement à apporter chaleur, dignité et protection aux communautés que nous accompagnons. Votre soutien permet de continuer ces distributions, d’envoyer du matériel là où il est le plus nécessaire et d’apporter un peu de réconfort aux personnes les plus fragiles.',
                'image' => 'https://images.unsplash.com/photo-1743800531573-2b1c442d3885?q=80&w=1170',
                'data' => [
                    ['country' => 'Maroc', 'count' => 4000, 'lat' => 31.7917, 'lng' => -7.0926],
                    ['country' => 'France', 'count' => 10000, 'lat' => 46.2276, 'lng' => 2.2137],
                ]
            ],
        ];

        foreach ($projects as $p) {
            $project = new Project();
            $project->setTitle($p['title']);
            $project->setSlug($p['slug']);
            $project->setDescription($p['description']);
            $project->setImage($p['image']);
            $project->setData($p['data']);
            $manager->persist($project);
        }

        $manager->flush();
    }
}
