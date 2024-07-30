<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\State\UserPasswordHasher;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;


class LoginController extends AbstractController
{
    public $passwordHasher;
    public $security;
    public $serializer;
    public $normalizer;
    public $jwtManager;
    public $databaseConnection;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
        SerializerInterface $serializer,
        NormalizerInterface $normalizer,
        JWTTokenManagerInterface $jwtManager,
        Connection $databaseConnection,
        private EntityManagerInterface $em
    ) {
        $this->security = $security;
        $this->passwordHasher = $passwordHasher;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->jwtManager = $jwtManager;
        $this->databaseConnection = $databaseConnection;
    }

    #[Route('/signup', name: 'app_login', methods: ['POST'])]
    public function index(): Response
    {
        $user = new Utilisateur();
        $user->setPseudo('loicrasoarahona');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'anjomakely');
        echo $hashedPassword;
        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }

    #[Route('/verifyUtilisateur', methods: ['POST'])]
    public function testLogin(Request $request)
    {
        // Cette fonction sert à vérifier un utilisateur sans générer de token
        $body = json_decode($request->getContent());
        $pseudo = $body->pseudo;
        $mdp = $body->mdp;
        $results = $this->em->getRepository(Utilisateur::class)->createQueryBuilder('user')
            ->select()
            ->where('user.pseudo=:pseudo')
            ->setParameter('pseudo', $pseudo)
            ->getQuery()
            ->getResult();
        if (count($results)) {
            $user = $results[0];
            $valid = $this->passwordHasher->isPasswordValid($user, $mdp);
            if ($valid)
                return new JsonResponse($valid);
        }
        return new JsonResponse(false, 401);
    }

    #[Route('/user_info', name: 'app_user_info', methods: ['GET'])]
    public function getUserInfo()
    {
        $user = $this->security->getUser();

        $normalizedUser = $this->serializer->normalize($user, null, ['groups' => ['user:collection', 'pointDeVente:collection']]);


        return $this->json($normalizedUser);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout()
    {
        throw new \Exception('should not be reached');
        return $this->json(["message" => "vous avez essayé de vous déconnecter"]);
    }

    // #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    // public function deconnexion()
    // {
    //     // Récupérez l'utilisateur actuel (optionnel)
    //     $user = $this->security->getUser();

    //     // Déconnectez l'utilisateur en invalidant sa session Symfony
    //     $this->security->getUser()->setLastLogout(new \DateTime()); // Facultatif : mettez à jour une propriété "lastLogout" par exemple
    //     $this->get('security.token_storage')->setToken(null);
    //     $this->get('session')->invalidate();

    //     return $response;
    // }
}
