<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        // Créer l'administrateur
        $admin = new User();
        $admin->setEmail('admin@storyforge.local');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        // Créer un utilisateur standard
        $user = new User();
        $user->setEmail('user@storyforge.local');
        $user->setUsername('testuser');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'user123')
        );
        $manager->persist($user);

        // Créer un utilisateur banni (pour les tests)
        $banned = new User();
        $banned->setEmail('banned@storyforge.local');
        $banned->setUsername('banneduser');
        $banned->setPassword(
            $this->passwordHasher->hashPassword($banned, 'banned123')
        );
        $banned->setIsBanned(true);
        $manager->persist($banned);

        // Créer quelques utilisateurs supplémentaires pour les tests
        $usernames = ['alice', 'bob', 'charlie', 'diana', 'emma'];
        foreach ($usernames as $i => $name) {
            $testUser = new User();
            $testUser->setEmail($name . '@storyforge.local');
            $testUser->setUsername($name);
            $testUser->setPassword(
                $this->passwordHasher->hashPassword($testUser, 'password123')
            );
            // Simuler des dates d'inscription différentes
            $testUser->setCreatedAt(new \DateTimeImmutable('-' . ($i * 2) . ' days'));
            $manager->persist($testUser);
        }

        $manager->flush();
    }
}
