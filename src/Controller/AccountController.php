<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zetta\ZendAuthentication\Contract\Entity\UserInterface;
use Zetta\ZendAuthentication\Entity\Enum\Gender;
use Zetta\ZendAuthentication\Form\PasswordChangeForm;
use Zetta\ZendAuthentication\Form\UserForm;

/**
 * Class AccountController
 * @method UserInterface identity()
 */
class AccountController extends AbstractActionController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
     * AccountController constructor.
     * @param EntityManagerInterface $entityManager
     * @param array $config
     */
    public function __construct(EntityManagerInterface $entityManager, array $config)
    {
        $this->entityManager = $entityManager;
        $this->setConfig($config);
    }

    /**
     * Get the AccountController config
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the AccountController config
     * @param array $config
     * @return AccountController
     */
    public function setConfig($config)
    {
        $this->config = $config;

        $this->routes = $config['routes'];
        $this->options = $config['options'];

        return $this;
    }

    /**
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        $user = $this->identity();

        $form = new UserForm($this->entityManager, 'user', $this->options);
        $form->setValidationGroup($form->getInputFilter()->getProfileValidationGroup());
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['account']['name'], $this->routes['account']['params'], $this->routes['account']['options'], $this->routes['account']['reuseMatchedParams']));
        $form->getInputFilter()->get('user')->get($this->options['identityProperty'])->setRequired($user->getUsername() !== null);
        $form->bind($user);
        $form->get('submit-btn')->setValue(_('Update'));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = ArrayUtils::merge(
                $request->getPost()->toArray(), $request->getFiles()->toArray()
            );
            $form->setData($post);

            if ($form->isValid()) {
                $this->thumbnail()->process($user->getAvatar(null), $user->getAvatar(null));
                if ($user->getAvatar(null) === $this->thumbnail()->getDefaultThumbnailPath() && $user->getGender() === Gender::FEMALE) {
                    $user->setAvatar($this->thumbnail()->getGirlThumbnailPath());
                } elseif ($user->getAvatar(null) === $this->thumbnail()->getGirlThumbnailPath() && $user->getGender() === Gender::MALE) {
                    $user->setAvatar($this->thumbnail()->getDefaultThumbnailPath());
                }
                $this->entityManager->flush();
                $this->flashMessenger()->addInfoMessage(_('Your profile has been updated.'));
                return $this->redirect()->toRoute($this->routes['account']['name'], $this->routes['account']['params'], $this->routes['account']['options'], $this->routes['account']['reuseMatchedParams']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('Your profile could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel([
            'form' => $form,
            'user' => $user,
            'routes' => $this->routes
        ]);

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     */
    public function passwordChangeAction()
    {
        $credentialRepo = $this->entityManager->getRepository($this->options['credentialClass']);
        /** @var UserInterface $user */
        $user = $this->identity();

        $form = new PasswordChangeForm();
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['password-change']['name'], $this->routes['password-change']['params'], $this->routes['password-change']['options'], $this->routes['password-change']['reuseMatchedParams']));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();
                $credential = $credentialRepo->findOneBy([$this->options['credentialIdentityProperty'] => $user, $this->options['credentialTypeProperty'] => $this->options['credentialType']]);

                if ($credential->verifyValue($data['password-old'])) {
                    $credential->setValue($data['password-new']);
                    $credential->hashValue();
                    $this->entityManager->flush();
                    $this->flashMessenger()->addSuccessMessage(_('Your password has been changed.'));
                    return $this->redirect()->toRoute($this->routes['account']['name'], $this->routes['account']['params'], $this->routes['account']['options'], $this->routes['account']['reuseMatchedParams']);
                } else {
                    $this->flashMessenger()->addErrorMessage(_('Your current password is incorrect. Please, try again.'));
                }
            } else {
                $this->flashMessenger()->addErrorMessage(_('Your password could not be saved. Please, try again.'));
            }
        }

        $form->prepare();
        $viewModel = new ViewModel([
            'form' => $form,
            'user' => $user,
            'routes' => $this->routes
        ]);

        return $viewModel;
    }
}
