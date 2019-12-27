<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Response;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Uri\Uri;
use Zend\View\Model\ViewModel;
use Zetta\ZendAuthentication\Authentication\Adapter\Credential;
use Zetta\ZendAuthentication\Authentication\Storage\Session;
use Zetta\ZendAuthentication\Contract\Entity\CredentialInterface;
use Zetta\ZendAuthentication\Contract\Entity\RoleInterface;
use Zetta\ZendAuthentication\Contract\Entity\UserInterface;
use Zetta\ZendAuthentication\Form\PasswordChangeForm;
use Zetta\ZendAuthentication\Form\RecoverForm;
use Zetta\ZendAuthentication\Form\SigninForm;
use Zetta\ZendAuthentication\Form\UserForm;

/**
 * Class AuthController
 * @method UserInterface identity()
 */
class AuthController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var AbstractPluginManager
     */
    protected $formManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $routes;

    /**
     * @var array
     */
    protected $templates;

    /**
     * @var string
     */
    protected $layoutView;

    /**
     * AuthController constructor.
     * @param EntityManager $entityManager
     * @param AuthenticationService $authenticationService
     * @param TranslatorInterface $translator
     * @param array $config
     */
    public function __construct(EntityManager $entityManager, AuthenticationService $authenticationService, TranslatorInterface $translator, $formManager, array $config)
    {
        $this->entityManager = $entityManager;
        $this->authenticationService = $authenticationService;
        $this->translator = $translator;
        $this->formManager = $formManager;
        $this->setConfig($config);
    }

    /**
     * Get the AuthController config
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the AuthController config
     * @param array $config
     * @return AuthController
     */
    public function setConfig($config)
    {
        $this->config = $config;

        $this->routes = $config['routes'];
        $this->templates = $config['templates'];
        $this->options = $config['options'];
        $this->layoutView = $config['layout'];

        return $this;
    }

    /**
     * @return Response|ViewModel
     * @throws Exception
     */
    public function signinAction()
    {
        $redirectUrl = trim($this->params()->fromQuery('redirect', ''));
        $options = $this->verifyRedirect($redirectUrl, $this->routes['authenticate']['options']);

        if ($this->identity()) {
            return $redirectUrl !== ''
                ? $this->redirect()->toUrl($redirectUrl)
                : $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
        }

        $form = new SigninForm();
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['authenticate']['name'], $this->routes['authenticate']['params'], $options, $this->routes['authenticate']['reuseMatchedParams']));
        $form->prepare();

        $viewModel = new ViewModel([
            'form' => $form,
            'routes' => $this->routes
        ]);

        $viewModel->setTemplate($this->templates['signin']);
        $this->layout($this->layoutView);

        return $viewModel;
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function authenticateAction()
    {
        $redirectUrl = trim($this->params()->fromQuery('redirect', ''));
        $options = $this->verifyRedirect($redirectUrl, $this->routes['authenticate']['options']);

        if ($this->identity()) {
            return $redirectUrl !== ''
                ? $this->redirect()->toUrl($redirectUrl)
                : $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
        }

        $form = new SigninForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                /** @var Credential $authAdapter */
                $authAdapter = $this->authenticationService->getAdapter();
                $authAdapter->setIdentity($data['username']);
                $authAdapter->setCredential($data['password']);

                $authResult = $this->authenticationService->authenticate();

                if ($authResult->isValid()) {
                    /** @var Session $authStorage */
                    $authStorage = $this->authenticationService->getStorage();
                    if ($data['remember-me'] === 1) {
                        $authStorage->setRememberMe(true);
                    }

                    $this->flashMessenger()->addSuccessMessage(_('You\'re conected!'));

                    return $redirectUrl !== ''
                        ? $this->redirect()->toUrl($redirectUrl)
                        : $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
                } else {
                    $this->flashMessenger()->addErrorMessage(_('Email or password is invalid.'));
                }
            }
        }

        return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $options, $this->routes['signin']['reuseMatchedParams']);
    }

    /**
     * @return Response
     */
    public function signoutAction()
    {
        if (!$this->identity()) {
            return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
        }

        $this->authenticationService->getStorage()->expireSessionCookie();
        $this->flashMessenger()->addErrorMessage(_('You\'re disconected!'));
        return $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
    }

    /**
     * @return Response|ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function signupAction()
    {
        if ($this->identity()) {
            return $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
        }

        $form = new UserForm($this->entityManager, 'signup', $this->options);
        $form->setValidationGroup($form->getInputFilter()->getSignupValidationGroup());
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['signup']['name'], $this->routes['signup']['params'], $this->routes['signup']['options'], $this->routes['signup']['reuseMatchedParams']));
        $identityClass = $this->options['identityClass'];
        /** @var UserInterface $user */
        $user = new $identityClass();
        $form->bind($user);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData(UserForm::VALUES_AS_ARRAY);
                $credentialClass = $this->options['credentialClass'];
                /** @var CredentialInterface $credential */
                $credential = new $credentialClass();
                $credential->setType($this->options['credentialType']);
                $credential->setValue($data['signup']['password']);
                $credential->hashValue();
                $credential->setUser($user);

                /** @var RoleInterface $role */
                $role = $this->entityManager->find($this->options['roleClass'], $this->config['default']['role']);
                $user->role($role);

                $user->setAvatar($this->thumbnail()->getDefaultThumbnailPath());
                $user->setSignAllowed($this->config['default']['signAllowed']);
                $user->generateToken(false);

                $this->entityManager->persist($user);
                $this->entityManager->persist($credential);
                $this->entityManager->flush();

                $fullLink = $this->url()->fromRoute(
                    $this->routes['confirm-email']['name'],
                    [
                        'token' => $user->getToken(),
                    ],
                    ['force_canonical' => true]
                );
                $to = $user->getEmail();
                $subject = $this->translator->translate('Please, confirm your registration!');
                $body = $this->translator->translate('Please, click the link to confirm your registration => ') . $fullLink;

                $this->email()->send($to, $subject, $body);

                $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate('An email has been sent to %s. Please, check your inbox and confirm your registration!'), $user->getEmail()));

                return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The account could not be created. Please, try again.'));
            }
        }

        $form->get('submit-btn')
            ->setValue(_('Sign me up'))
            ->setAttribute('class', 'btn btn-lg btn-block btn-primary');
        $form->prepare();
        $viewModel = new ViewModel([
            'form' => $form,
            'routes' => $this->routes
        ]);

        $viewModel->setTemplate($this->templates['signup']);
        $this->layout($this->layoutView);

        return $viewModel;
    }

    /**
     * @return Response
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function confirmEmailAction()
    {
        $identityRepo = $this->entityManager->getRepository($this->options['identityClass']);
        $token = $this->params()->fromRoute('token', 0);

        if ($this->identity()) {
            $this->authenticationService->getStorage()->expireSessionCookie();
        }

        $qb = $identityRepo->createQueryBuilder('identity');
        $qb->where('identity.token = :token');
        $qb->setParameter('token', $token);
        /** @var UserInterface $identity */
        $identity = $qb->getQuery()->getOneOrNullResult();

        if ($identity === null || $identity->getTokenDate() !== null) {
            if ($identity !== null) {
                $identity->unsetToken();
                $this->entityManager->flush();
            }
            $this->flashMessenger()->addErrorMessage(_('Token invalid or you already confirmed this link.'));
            return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
        }

        if (!$identity->isConfirmedEmail()) {
            $identity->unsetToken(); // unset immediately taken to prevent multiple requests to db
            $identity->setSignAllowed(true);
            $identity->setConfirmedEmail(true);
            $this->entityManager->flush();
            $this->authenticationService->getStorage()->write($identity);
            $this->flashMessenger()->addSuccessMessage(_('The email has been confirmed.'));
            return $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
        } else {
            $identity->unsetToken(); // unset immediately taken to prevent multiple requests to db
            $this->entityManager->flush();
            $this->flashMessenger()->addInfoMessage(_('Email already verified. Please, sign in.'));
            return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
        }
    }

    /**
     * @return Response|ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function recoverAction()
    {
        $identityRepo = $this->entityManager->getRepository($this->options['identityClass']);
        if ($this->identity()) {
            return $this->redirect()->toRoute($this->routes['redirect']['name'], $this->routes['redirect']['params'], $this->routes['redirect']['options'], $this->routes['redirect']['reuseMatchedParams']);
        }

        $form = $this->formManager->get(RecoverForm::class);
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['recover']['name'], $this->routes['recover']['params'], $this->routes['recover']['options'], $this->routes['recover']['reuseMatchedParams']));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                /** @var UserInterface $identity */
                $identity = $identityRepo->findOneBy([$this->options['emailProperty'] => $form->get('email')->getValue()]);
                if (is_null($identity)) {
                    $this->flashMessenger()->addErrorMessage('Email not found');
                } else {
                    $identity->generateToken();
                    $this->entityManager->flush();

                    $fullLink = $this->url()->fromRoute(
                        $this->routes['password-recover']['name'],
                        [
                            'token' => $identity->getToken(),
                        ],
                        ['force_canonical' => true]
                    );
                    $to = $identity->getEmail();
                    $subject = $this->translator->translate('Recover password!');
                    $body = $this->translator->translate('Please, click the link to recover your password => ') . $fullLink;

                    $this->email()->send($to, $subject, $body);

                    $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate('An email has been sent to %s. Please, check your inbox and recover your password!'), $identity->getEmail()));

                    return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
                }
            } else {
                $this->flashMessenger()->addErrorMessage(_('The action could not be completed. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel([
            'form' => $form,
            'routes' => $this->routes
        ]);

        $viewModel->setTemplate($this->templates['recover']);
        $this->layout($this->layoutView);

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function passwordRecoverAction()
    {
        $identityRepo = $this->entityManager->getRepository($this->options['identityClass']);
        $credentialRepo = $this->entityManager->getRepository($this->options['credentialClass']);
        $token = $this->params()->fromRoute('token', 0);

        if ($this->identity()) {
            $this->authenticationService->getStorage()->expireSessionCookie();
        }

        $qb = $identityRepo->createQueryBuilder('identity');
        $qb->where('identity.token = :token');
        $qb->setParameter('token', $token);
        /** @var UserInterface $identity */
        $identity = $qb->getQuery()->getOneOrNullResult();

        $now = new DateTime();
        if ($identity === null || $identity->getTokenDate() === null
            || $now->getTimestamp() - $identity->getTokenDate()->getTimestamp() > 86400) {
            if ($identity !== null) {
                $identity->unsetToken();
                $this->entityManager->flush();
            }
            $this->flashMessenger()->addErrorMessage(_('Token invalid or you already confirmed this link.'));
            return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
        }

        $form = new PasswordChangeForm();
        $form->getInputFilter()->get('password-old')->setRequired(false);
        $this->routes['password-recover']['params']['token'] = $token;
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['password-recover']['name'], $this->routes['password-recover']['params'], $this->routes['password-recover']['options'], $this->routes['password-recover']['reuseMatchedParams']));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                /** @var CredentialInterface $credential */
                $credential = $credentialRepo->findOneBy([$this->options['credentialIdentityProperty'] => $identity, $this->options['credentialTypeProperty'] => $this->options['credentialType']]);
                $identity->unsetToken();
                $credential->setValue($data['password-new']);
                $credential->hashValue();
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage(_('Your password has been changed. Please, sign in.'));
                return $this->redirect()->toRoute($this->routes['signin']['name'], $this->routes['signin']['params'], $this->routes['signin']['options'], $this->routes['signin']['reuseMatchedParams']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('The action could not be completed. Please, try again.'));
            }
        }

        $form->get('submit-btn')->setAttribute('class', 'btn btn-lg btn-block btn-primary');
        $form->prepare();
        $viewModel = new ViewModel([
            'user' => $identity,
            'form' => $form,
            'routes' => $this->routes
        ]);

        $viewModel->setTemplate($this->templates['password-recover']);
        $this->layout($this->layoutView);

        return $viewModel;
    }

    /**
     * Verify redirect
     * @param string $redirectUrl
     * @param array $options
     * @return array
     * @throws Exception
     */
    protected function verifyRedirect($redirectUrl, $options)
    {
        if (strlen($redirectUrl) > 2048) {
            throw new Exception('Too long redirectUrl argument passed');
        }
        if ($redirectUrl !== '') {
            // The below check is to prevent possible redirect attack
            // (if someone tries to redirect user to another domain).
            $uri = new Uri($redirectUrl);
            if (!$uri->isValid() || $uri->getHost() !== null) {
                throw new Exception('Incorrect redirect URL: ' . $redirectUrl);
            }
            $options = ArrayUtils::merge($options, ['query' => ['redirect' => $redirectUrl]]);
        }
        return $options;
    }
}
