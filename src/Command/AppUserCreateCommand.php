<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AppUser;

#[AsCommand(
    name: 'app:user:create',
    description: 'Add a short description for your command',
)]
class AppUserCreateCommand extends Command
{
    protected $em;
    protected $passwordHasher;
    protected $validator;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator)
    {
        $this->em = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the user to register')
            ->addOption('email', null, InputArgument::VALUE_REQUIRED, 'Optionnal email of the user to register')
            ->addOption('password', "p", InputOption::VALUE_REQUIRED, 'Password of the user. If unspecified, a prompt will open. It is also advised touse an environment variable to avoid leaks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Start with some input validation
        $username = $input->getArgument('name');
        $email = $input->getOption('email');
        if ($email !== null && strlen($email) > 0) {
            $emailConstraint = new Assert\Email();
            // use the validator to validate the value
            if (count($this->validator->validate($email, $emailConstraint)) > 0) {
                $io->error(sprintf("The given email is invalid (\"%s\")", $email));
                return 1;
            }
        }

        // Prompt the password of not already given
        $password = $input->getOption('password');
        if (!$password) {
            $password = $io->askHidden('What password should the new user have?');
        }

        $passwordConstraint = new Assert\Length(null, 8);
        if (count($this->validator->validate($password, $passwordConstraint)) > 0) {
            $io->error("The given password is too short");
            return 1;
        }

        $matchingUsernames = $this->em->getRepository()->findBy(['username' => $username]);
        if (count($matchingUsernames)) {
            $io->info("The requested user already exists.");

            return Command::SUCCESS;
        }
        if ($email !== null && strlen($email) > 0) {
            $matchingEmail = $this->em->getRepository()->findBy(['email' => $email]);
            if (count($matchingEmail)) {
                $io->info("The requested user already exists.");

                return Command::SUCCESS;
            }
        }

        $newUser = new AppUser();
        $newUser->setUsername($username);
        $newUser->setEmail($email);

        $encryptedPassword = $this->passwordHasher->hashPassword($newUser, $password);
        $newUser->setPassword($encryptedPassword);

        $this->em->persist($newUser);
        $this->em->flush();

        $io->success('The new user has been created.');

        return Command::SUCCESS;
    }
}
