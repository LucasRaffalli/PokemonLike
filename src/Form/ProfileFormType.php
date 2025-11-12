<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un pseudo',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre pseudo doit contenir au moins {{ limit }} caractères',
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un email',
                    ]),
                    new Email([
                        'message' => 'Veuillez entrer un email valide',
                    ]),
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel (requis pour valider les modifications)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe actuel',
                    ]),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe (optionnel)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
