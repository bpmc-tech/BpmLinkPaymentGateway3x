<?php

/*
 * This file is part of the BpmLinkPaymentGateway
 *
 * Copyright (C) 2017 K.Matsuki <info@bpmc.co.jp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BpmLinkPaymentGateway\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Plugin\BpmLinkPaymentGateway\Controller\Util\PluginUtil;

class BpmLinkPaymentGatewayConfigType extends AbstractType
{

    private $app;
    
    public function __construct(\Eccube\Application $app, $subData = null)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $pluginUtil = & PluginUtil::getInstance($this->app);
        $pluginCode = $pluginUtil->getCode(true);
        $BpmcPlugin = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin')->findOneBy(array('code' => $pluginCode));

        $paymentTypes = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->getSelectedTypes();

        if(is_null($BpmcPlugin)){
            $BpmcPlugin = new \Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin();
        }

        $builder
            ->add('api_token', 'text', array(
                'label' => 'API TOKEN',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ API TOKENが入力されていません。')),
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'data' => $BpmcPlugin->getApiToken(),
            ));
    }

    public function getName()
    {
        return 'bpmlinkpaymentgateway_config';
    }

}
