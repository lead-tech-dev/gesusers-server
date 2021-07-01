<?php


namespace App\Serializer;


use ApiPlatform\Core\Exception\RuntimeException;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\User;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var SerializerContextBuilderInterface
     */
    private $decorated;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    public function __construct(SerializerContextBuilderInterface $decorated,
    AuthorizationCheckerInterface $authorizationChecker)
    {
      $this->decorated = $decorated;
      $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Creates a serialization context from a Request
     * @param Request $request
     * @param bool $normalization
     * @param array|null $extractedAttributes
     * @throws RuntimeCommandException
     * @return array
     */
    public function createFromRequest(
        Request $request,
        bool $normalization,
        array $extractedAttributes = null
    ): array
    {
       $content = $this->decorated->createFromRequest(
           $request, $normalization, $extractedAttributes
       );

        $resourceClass = $content['resource_class'] ?? null; 

        if (
            User::class === $resourceClass &&
            isset($content['groups']) &&
            $normalization === true &&
            $this->authorizationChecker->isGranted(User::ROLE_ADMIN)
        ){
            $content['groups'][] = 'get-admin';
        }

        return $content;
    }
}