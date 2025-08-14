<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Crée un administrateur (username/password) avec hash sécurisé.'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Nom d\'utilisateur admin')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $username = $input->getArgument('username');
        if (!$username) {
            $question = new Question('Username de l\'admin: ');
            $username = $helper->ask($input, $output, $question);
        }
        $username = strtolower(trim((string)$username));
        if ($username === '') {
            $output->writeln('<error>Username invalide.</error>');
            return Command::FAILURE;
        }

        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question('Mot de passe de l\'admin: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }
        if (!\is_string($password) || strlen($password) < 6) {
            $output->writeln('<error>Le mot de passe doit contenir au moins 6 caractères.</error>');
            return Command::FAILURE;
        }

        // Vérifier unicité
        $existing = $this->em->getRepository(Admin::class)->findOneBy(['username' => $username]);
        if ($existing) {
            $output->writeln('<error>Un admin avec ce username existe déjà.</error>');
            return Command::FAILURE;
        }

        $admin = new Admin();
        $admin->setUsername($username);
        $hashed = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashed);

        $this->em->persist($admin);
        $this->em->flush();

        $output->writeln('<info>Admin créé avec succès.</info>');
        $output->writeln(sprintf('Username: %s', $admin->getUserIdentifier()));

        return Command::SUCCESS;
    }
}
