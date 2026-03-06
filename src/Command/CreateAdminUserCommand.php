<?php

namespace App\Command;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Creates an admin user: nayrouzdaikhi@gmail.com with password nayrouz123'
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = 'nayrouzdaikhi@gmail.com';
        $password = 'nayrouz123';
        
        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            $output->writeln("<fg=yellow>⚠️  User already exists: $email</>");
            return Command::SUCCESS;
        }
        
        // Create new user
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Nayrouzdaikhi');
        $user->setLastName('Admin');
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Set roles and status
        $user->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $user->setStatus('UNBLOCKED');
        
        // Set timestamps
        $now = new DateTime();
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        
        // Save to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $output->writeln("<fg=green>✅ Admin user created successfully!</>");
        $output->writeln("   Email: $email");
        $output->writeln("   Password: $password");
        $output->writeln("   Roles: ROLE_SUPER_ADMIN, ROLE_USER");
        $output->writeln("   Face Recognition: Not required (optional)");
        $output->writeln("");
        $output->writeln("You can now login with this account on the login page.");
        
        return Command::SUCCESS;
    }
}
