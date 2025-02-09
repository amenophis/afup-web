<?php


namespace AppBundle\Event\Form;

use AppBundle\Event\Model\GithubUser;
use AppBundle\Event\Model\Repository\GithubUserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpeakerType extends AbstractType
{
    const OPT_PHOTO_REQUIRED = 'photo_required';
    const OPT_USER_GITHUB = 'user_github';

    /** @var GithubUserRepository */
    private $githubUserRepository;

    public function __construct(GithubUserRepository $githubUserRepository)
    {
        $this->githubUserRepository = $githubUserRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('civility', ChoiceType::class, ['choices' => ['M' => 'M', 'Mme' => 'Mme']])
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('company', TextType::class, ['required' => false])
            ->add('locality', TextType::class, ['required' => false, 'help' => 'Indiquer votre ville nous permet de mieux anticiper les déplacements'])
            ->add('biography', TextareaType::class)
            ->add('twitter', TextType::class, ['required' => false])
        ;
        if (true === $options[self::OPT_USER_GITHUB]) {
            $builder
                ->add('github_user',
                    ChoiceType::class,
                    [
                        'property_path' => 'githubUser',
                        'label' => 'Utilisateur GitHub',
                        'required' => false,
                        'choice_label' => function (GithubUser $user) {
                            return $user->getLabel();
                        },
                        'choice_value' => function ($choice) {
                            if ($choice instanceof GithubUser) {
                                return $choice->getId();
                            }
                            return $choice;
                        },
                        'choice_loader' => new CallbackChoiceLoader(function () {
                            return $this->githubUserRepository->getAllOrderedByLogin();
                        }),
                    ]
                )
            ;
        }

        $builder
            ->add('photo', FileType::class, ['label' => 'Photo de profil', 'data_class' => null, 'required' => $options[self::OPT_PHOTO_REQUIRED]])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                self::OPT_PHOTO_REQUIRED => true,
                self::OPT_USER_GITHUB => false,
            ])
        ;
    }
}
