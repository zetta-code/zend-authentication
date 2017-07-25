<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zetta\ZendAuthentication\Entity\UserInterface;
use Zetta\ZendAuthentication\Form\PasswordChangeForm;
use Zetta\ZendAuthentication\Form\UserForm;
use Zetta\ZendAuthentication\InputFilter\UserFilter;

class AccountController extends AbstractActionController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
        $this->setOptions($config);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {

        $this->routes = $options['routes'];
        $this->options = $options['options'];
    }

    public function indexAction()
    {
        /** @var UserInterface $user */
        $user = $this->identity();

        $form = new UserForm($this->entityManager, 'user', $this->options);
        $form->setValidationGroup(UserFilter::VALIDATION_PROFILE);
        $form->setAttribute('action', $this->url()->fromRoute($this->routes['account']['name'], $this->routes['account']['params'], $this->routes['account']['options'], $this->routes['account']['reuseMatchedParams']));
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
                $this->entityManager->flush();
                $this->flashMessenger()->addInfoMessage(_('Profile updated with success!'));
                return $this->redirect()->toRoute($this->routes['account']['name'], $this->routes['account']['params'], $this->routes['account']['options'], $this->routes['account']['reuseMatchedParams']);
            } else {
                $this->flashMessenger()->addErrorMessage(_('Form with errors!'));
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
                $credential = $credentialRepo->findOneBy([$this->options['credentialIdentityProperty'] => $user, 'type' => $this->options['credentialType']]);
                $passwordOld = sha1(sha1($data['password-old']));
                $passwordNew = sha1(sha1($data['password-new']));
                $password = $credential->getValue();

                if ($password == $passwordOld) {
                    $credential->setValue($passwordNew);
                    $this->entityManager->flush();
                    $this->flashMessenger()->addSuccessMessage(_('Your password has been changed successfully!'));
                    return $this->redirect()->toRoute($this->routes['account']['name'], $this->routes['account']['params'], $this->routes['account']['options'], $this->routes['account']['reuseMatchedParams']);
                } else {
                    $this->flashMessenger()->addErrorMessage(_('Your current password is incorrect.'));
                }
            } else {
                $this->flashMessenger()->addErrorMessage(_('Form with errors!'));
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
