<?php

namespace OrangeHRM\Help\Controller;

use Exception;
use OrangeHRM\Authentication\Controller\ForbiddenController;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Controller\Exception\RequestForwardableException;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Help\Service\HelpService;

class HelpController extends AbstractVueController
{
    protected ?HelpService $helpService = null;

    public function getHelpService(): HelpService
    {
        return $this->helpService ??= new HelpService();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws RequestForwardableException
     */
    public function handle(Request $request)
    {
        if ($this->getHelpService()->isValidUrl()) {
            try {
                $label = $request->query->get('label');
                $redirectUrl = $this->getHelpService()->getRedirectUrl($label);
                return new RedirectResponse($redirectUrl);
            } catch (Exception $e) {
                $defaultRedirectUrl = $this->getHelpService()->getDefaultRedirectUrl();
                return new RedirectResponse($defaultRedirectUrl);
            }
        } else {
            throw new RequestForwardableException(ForbiddenController::class . '::handle');
        }
    }
}
