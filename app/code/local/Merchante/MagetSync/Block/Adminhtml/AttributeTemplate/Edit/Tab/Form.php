<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class for creating template form
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tab_Form
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldsetGlobal = $form->addFieldset('magetsync_form_global_section',
            array('legend'=>Mage::helper('magetsync')->__("Global Section")));

        $fieldsetGlobal->addField('prepended_template', 'select', array(
            'name'  => 'prepended_template',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Prepend Global Note"),
            'values'    => array(
                null => 'Select template',
                1   => Mage::helper('magetsync')->__('Note').' 1',
                2   => Mage::helper('magetsync')->__('Note').' 2'
            ),
        ));
        $fieldsetGlobal->addField('appended_template', 'select', array(
            'name'  => 'appended_template',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Append Global Note"),
            'values'    => array(
                null => 'Select template',
                1   => Mage::helper('magetsync')->__('Note').' 1',
                2   => Mage::helper('magetsync')->__('Note').' 2'
            ),
        ));

        $fieldsetGlobal->addField('should_auto_renew', 'select', array(
            'name'  => 'should_auto_renew',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Automatically renew listing"),
            'values'    => array(
                null => 'Select renewal option',
                1   => Mage::helper('magetsync')->__('Yes'),
                0   => Mage::helper('magetsync')->__('No')
            ),
        ));


        $fieldsetAbout = $form->addFieldset('magetsync_form_about',
            array('legend'=>Mage::helper('magetsync')->__("About information")));

        $whoMade = $fieldsetAbout->addField('who_made', 'select', array(
            'name'  => 'who_made',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("Who Made it?"),
            'values'    => Mage::getModel('magetsync/whoMade')->toOptionArray(),
            'onchange' => 'getNextData(this)',
        ));

        $fieldsetAbout->addField('is_supply', 'select', array(
            'name'  => 'is_supply',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("What is it?"),
            'disabled' => true,
            'values'    => array(
                null => 'Select a use',
                1   => Mage::helper('magetsync')->__('A finished product'),
                2   => Mage::helper('magetsync')->__('A supply or tool to make things')
            ),
        ));

        $fieldsetAbout->addField('when_made', 'select', array(
            'name'  => 'when_made',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("When was it made?"),
            'disabled' => true,
            'values'    => Mage::getModel('magetsync/whenMade')->toOptionArray(),
        ));

        $fieldsetAbout->addType('pricingrule', 'Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Renderer_Pricing');
        $pricingRule = $fieldsetAbout->addField('pricing_rule', 'pricingrule', array(
            'name'  => 'pricing_rule',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("Pricing Rule"),
            'disabled' => false,
            'rules'    => Mage::getModel('magetsync/attributeTemplate')->toPricingRuleOptionArray(),
            'strategies'    => Mage::getModel('magetsync/attributeTemplate')->toPricingStrategyOptionArray(),
            'style'     => 'width:100px;',
        ));
        $pricingRule->setAfterElementHtml("
            <script type=\"text/javascript\">
                $('affect_value').observe('keyup', calculateEstimatePrice);
                $('affect_strategy').observe('change', calculateEstimatePrice);
                function calculateEstimatePrice(reset) {
                    var affectPriceVal = document.getElementById('affect_value');
                    var priceValue = 10;
                    if (reset !== true) {
                        if (document.getElementById('affect_strategy').value == 'percentage') {
                            var delta = priceValue * Number(affectPriceVal.value)/100;
                        } else {
                            var delta = Number(affectPriceVal.value);
                        }
                        if (document.getElementById('pricing_rule').value == 'increase') {
                            priceValue += delta;
                        } else {
                            priceValue -= delta;
                        }
                    }

                    document.getElementById('estimate-price').update(priceValue);
                }
                function togglePricing(select) {
                    if (select.value != 'original') {
                        document.getElementById('affect_value').show();
                        document.getElementById('affect_strategy').show();
                        calculateEstimatePrice();
                    } else {
                        document.getElementById('affect_value').hide();
                        document.getElementById('affect_strategy').hide();
                        calculateEstimatePrice(true);
                    }
                }
                togglePricing($('pricing_rule'));
            </script>
        ");

        /**
         * Validation on event 'change'
         */
        $whoMade->setAfterElementHtml("
                <script type=\"text/javascript\">
                    function getNextData(selectElement){
                         if(selectElement.value != '')
                         {
                            document.getElementById('is_supply').disabled = false;
                            $('is_supply').observe('change', function(e){
                                  var value = document.getElementById('is_supply').value;
                                  if(value != null)
                                  {
                                    document.getElementById('when_made').disabled = false;
                                  }
                                  else
                                  {
                                    document.getElementById('when_made').value = '';
                                    document.getElementById('when_made').disabled = true;
                                  }
                             });
                         }
                         else
                         {
                            document.getElementById('is_supply').value = '';
                            document.getElementById('when_made').value = '';
                            document.getElementById('is_supply').disabled = true;
                            document.getElementById('when_made').disabled = true;
                         }
                    }
                </script>");


        $dataMagetsy = Mage::registry('magetsync_data');
        $filtersub = '';
        $filtersubsub = '';
        $filtersub4 = '';
        $filtersub5 = '';
        $filtersub6 = '';
        $filtersub7 = '';

        if ( $dataMagetsy )
        {
            if($dataMagetsy['category_id']<>'')
            {
                $filtersub = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['category_id']);
            }
            if($dataMagetsy['subcategory_id']<>'')
            {
                $filtersubsub = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory_id']);
            }

            if($dataMagetsy['subsubcategory_id']<>'')
            {
                $filtersub4 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subsubcategory_id']);
            }

            if($dataMagetsy['subcategory4_id']<>'')
            {
                $filtersub5 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory4_id']);
            }

            if($dataMagetsy['subcategory5_id']<>'')
            {
                $filtersub6 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory5_id']);
            }

            if($dataMagetsy['subcategory6_id']<>'')
            {
                $filtersub7 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory6_id']);
            }
        }

        $fieldsetCategories = $form->addFieldset('magetsync_form_category',
            array('legend'=> Mage::helper('magetsync')->__("Category information")));

        $category = $fieldsetCategories->addField('category_id', 'select', array(
            'name'  => 'category_id',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("Category"),
            'values'    => Mage::getModel('magetsync/category')->toOptionArray(),
            'onchange' => 'getCategory(this)',
        ));

        $subCategory = $fieldsetCategories->addField('subcategory_id', 'select', array(
            'name'  => 'subcategory_id',
            'label'     => Mage::helper('magetsync')->__("Category 2"),
            'required' => false,
            'values' => $filtersub,
            'onchange' => 'getSubCategory(this)'
        ));

        $subSubCategory = $fieldsetCategories->addField('subsubcategory_id', 'select', array(
            'name'  => 'subsubcategory_id',
            'label'     => Mage::helper('magetsync')->__("Category 3"),
            'required' => false,
            'values' =>  $filtersubsub,
            'onchange' => 'getSubSubCategory(this)'
        ));

        $subCategory4 = $fieldsetCategories->addField('subcategory4_id', 'select', array(
            'name'  => 'subcategory4_id',
            'label'     => Mage::helper('magetsync')->__("Category 4"),
            'required' => false,
            'values' =>  $filtersub4,
            'onchange' => 'getSubCategory4(this)'
        ));

        $subCategory5 = $fieldsetCategories->addField('subcategory5_id', 'select', array(
            'name'  => 'subcategory5_id',
            'label'     => Mage::helper('magetsync')->__("Category 5"),
            'required' => false,
            'values' =>  $filtersub5,
            'onchange' => 'getSubCategory5(this)'
        ));

        $subCategory6 = $fieldsetCategories->addField('subcategory6_id', 'select', array(
            'name'  => 'subcategory6_id',
            'label'     => Mage::helper('magetsync')->__("Category 6"),
            'required' => false,
            'values' =>  $filtersub6,
            'onchange' => 'getSubCategory6(this)'
        ));

        $subCategory7 = $fieldsetCategories->addField('subcategory7_id', 'select', array(
            'name'  => 'subcategory7_id',
            'label'     => Mage::helper('magetsync')->__("Category 7"),
            'required' => false,
            'values' =>  $filtersub7
        ));

        /**
         * Script for dependents selects (Use ajax)
         */
        $category->setAfterElementHtml("<script type=\"text/javascript\">

                function getCategory(selectElement){

                    $('subcategory_id').up(0).up(0).hide();
                    $('subsubcategory_id').up(0).up(0).hide();
                    $('subcategory4_id').up(0).up(0).hide();
                    $('subcategory5_id').up(0).up(0).hide();
                    $('subcategory6_id').up(0).up(0).hide();
                    $('subcategory7_id').up(0).up(0).hide();

                    if(selectElement.value == '')
                    {
                        $('subcategory_id').update('');
                        $('subsubcategory_id').update('');
                        $('subcategory4_id').update('');
                        $('subcategory5_id').update('');
                        $('subcategory6_id').update('');
                        $('subcategory7_id').update('');

                        return false;
                    }
                    var reloadurl = '".$this->getUrl('adminhtml/magetsync_index/category') . "tag/' + selectElement.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (subform) {
                            $('subcategory_id').update('');
                            $('subcategory_id').update('Searching…');
                        },
                        onComplete: function(subform) {
                             if(subform.responseText == '')
                             {
                                $('subcategory_id').up(0).up(0).hide();
                                $('subsubcategory_id').up(0).up(0).hide();
                                $('subcategory4_id').up(0).up(0).hide();
                                $('subcategory5_id').up(0).up(0).hide();
                                $('subcategory6_id').up(0).up(0).hide();
                                $('subcategory7_id').up(0).up(0).hide();
                             }else
                             {
                              $('subcategory_id').up(0).up(0).show();
                              $('subcategory_id').update(subform.responseText);
                             }
                             }
                    });

                }
                </script>");

        $subCategory->setAfterElementHtml("<script type=\"text/javascript\">
                function getSubCategory(selectElement){
                    $('subsubcategory_id').up(0).up(0).hide();
                    $('subcategory4_id').up(0).up(0).hide();
                    $('subcategory5_id').up(0).up(0).hide();
                    $('subcategory6_id').up(0).up(0).hide();
                    $('subcategory7_id').up(0).up(0).hide();

                    if(selectElement.value == '')
                    {
                        $('subsubcategory_id').update('');
                        $('subcategory4_id').update('');
                        $('subcategory5_id').update('');
                        $('subcategory6_id').update('');
                        $('subcategory7_id').update('');

                        return false;
                    }
                    var reloadurl = '".$this->getUrl('adminhtml/magetsync_index/category') . "tag/' + selectElement.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (subform) {
                            $('subsubcategory_id').update('');
                            $('subsubcategory_id').update('Searching…');
                        },
                        onComplete: function(subform) {
                             if(subform.responseText == '')
                             {
                                $('subsubcategory_id').up(0).up(0).hide();
                                $('subcategory4_id').up(0).up(0).hide();
                                $('subcategory5_id').up(0).up(0).hide();
                                $('subcategory6_id').up(0).up(0).hide();
                                $('subcategory7_id').up(0).up(0).hide();
                             }else
                             {
                              $('subsubcategory_id').up(0).up(0).show();
                              $('subsubcategory_id').update(subform.responseText);
                             }
                            }
                    });

                }
                </script>");

        $subSubCategory->setAfterElementHtml("<script type=\"text/javascript\">
                function getSubSubCategory(selectElement){
                    $('subcategory4_id').up(0).up(0).hide();
                    $('subcategory5_id').up(0).up(0).hide();
                    $('subcategory6_id').up(0).up(0).hide();
                    $('subcategory7_id').up(0).up(0).hide();

                    if(selectElement.value == '')
                    {
                        $('subcategory4_id').update('');
                        $('subcategory5_id').update('');
                        $('subcategory6_id').update('');
                        $('subcategory7_id').update('');

                        return false;
                    }
                    var reloadurl = '".$this->getUrl('adminhtml/magetsync_index/category') . "tag/' + selectElement.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (subform) {
                            $('subcategory4_id').update('');
                            $('subcategory4_id').update('Searching…');
                        },
                        onComplete: function(subform) {
                             if(subform.responseText == '')
                             {
                                $('subcategory4_id').up(0).up(0).hide();
                                $('subcategory5_id').up(0).up(0).hide();
                                $('subcategory6_id').up(0).up(0).hide();
                                $('subcategory7_id').up(0).up(0).hide();
                             }else
                             {
                              $('subcategory4_id').up(0).up(0).show();
                              $('subcategory4_id').update(subform.responseText);
                             }
                            }
                    });

                }
                </script>");

        $subCategory4->setAfterElementHtml("<script type=\"text/javascript\">
                function getSubCategory4(selectElement){
                    $('subcategory5_id').up(0).up(0).hide();
                    $('subcategory6_id').up(0).up(0).hide();
                    $('subcategory7_id').up(0).up(0).hide();

                    if(selectElement.value == '')
                    {
                        $('subcategory5_id').update('');
                        $('subcategory6_id').update('');
                        $('subcategory7_id').update('');

                        return false;
                    }
                    var reloadurl = '".$this->getUrl('adminhtml/magetsync_index/category') . "tag/' + selectElement.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (subform) {
                            $('subcategory5_id').update('');
                            $('subcategory5_id').update('Searching…');
                        },
                        onComplete: function(subform) {
                             if(subform.responseText == '')
                             {
                                $('subcategory5_id').up(0).up(0).hide();
                                $('subcategory6_id').up(0).up(0).hide();
                                $('subcategory7_id').up(0).up(0).hide();
                             }else
                             {
                              $('subcategory5_id').up(0).up(0).show();
                              $('subcategory5_id').update(subform.responseText);
                             }
                          }
                    });

                }
                </script>");

        $subCategory5->setAfterElementHtml("<script type=\"text/javascript\">
                function getSubCategory5(selectElement){
                    $('subcategory6_id').up(0).up(0).hide();
                    $('subcategory7_id').up(0).up(0).hide();

                    if(selectElement.value == '')
                    {
                        $('subcategory6_id').update('');
                        $('subcategory7_id').update('');

                        return false;
                    }
                    var reloadurl = '".$this->getUrl('adminhtml/magetsync_index/category') . "tag/' + selectElement.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (subform) {
                            $('subcategory6_id').update('');
                            $('subcategory6_id').update('Searching…');
                        },
                        onComplete: function(subform) {
                             if(subform.responseText == '')
                             {
                                $('subcategory6_id').up(0).up(0).hide();
                                $('subcategory7_id').up(0).up(0).hide();
                             }else
                             {
                              $('subcategory6_id').up(0).up(0).show();
                              $('subcategory6_id').update(subform.responseText);
                             }
                          }
                    });

                }
                </script>");

        $subCategory6->setAfterElementHtml("<script type=\"text/javascript\">
                function getSubCategory6(selectElement){
                    $('subcategory7_id').up(0).up(0).hide();

                    if(selectElement.value == '')
                    {
                        $('subcategory7_id').update('');

                        return false;
                    }
                    var reloadurl = '".$this->getUrl('adminhtml/magetsync_index/category') . "tag/' + selectElement.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (subform) {
                            $('subcategory7_id').update('');
                            $('subcategory7_id').update('Searching…');
                        },
                        onComplete: function(subform) {
                             if(subform.responseText == '')
                             {
                                $('subcategory7_id').up(0).up(0).hide();
                             }else
                             {
                              $('subcategory7_id').up(0).up(0).show();
                              $('subcategory7_id').update(subform.responseText);
                             }
                          }
                    });

                }
                </script>");

        $fieldsetSearch = $form->addFieldset('magetsync_form_search',
            array('legend'=> Mage::helper('magetsync')->__("Search information")));

        $fieldsetSearch->addField('recipient', 'select', array(
            'name'  => 'recipient',
            'label'     => Mage::helper('magetsync')->__("Recipient"),
            'values'    => Mage::getModel('magetsync/recipient')->toOptionArray(),
        ));

        $fieldsetSearch->addField('occasion', 'select', array(
            'name'  => 'occasion',
            'label'     => Mage::helper('magetsync')->__("Occasion"),
            'values'    => Mage::getModel('magetsync/occasion')->toOptionArray(),
        ));

        $fieldsetSearch->addField('materials', 'text',
            array(
                'label' => Mage::helper('magetsync')->__("Materials"),
                'name' => 'materials',
            ));

        $style1 = $fieldsetSearch->addField('style_one', 'select',
            array(
                'label' => Mage::helper('magetsync')->__("Style").' 1',
                'name' => 'style_one',
                'required' => false,
                'values'    => Mage::getModel('magetsync/style')->toOptionArray(),
                'onchange' => 'enableStyle2(this)'
            ));

        $style1->setAfterElementHtml("<script type=\"text/javascript\">
                function enableStyle2(selectElement){
                    if(selectElement.value == '')
                    {

                        $('style_two').value = '';
                        $('style_two').up(0).up(0).hide();
                    }else
                    {
                        $('style_two').up(0).up(0).show();
                    }
                }
                </script>");

        $style2 = $fieldsetSearch->addField('style_two', 'select',
            array(
                'label' => Mage::helper('magetsync')->__("Style").' 2',
                'name' => 'style_two',
                'required' => false,
                'values'    => Mage::getModel('magetsync/style')->toOptionArray()
            ));


        $fieldsetSS = $form->addFieldset('magetsync_form_ss',
            array('legend'=> Mage::helper('magetsync')->__("Shipping and Shop information")));

        $fieldsetSS->addField('shop_section_id', 'select', array(
            'name'  => 'shop_section_id',
            'label'     => Mage::helper('magetsync')->__("Shop section"),
            'class'  => 'validate-select',
            'required' => true,
            'values'    => Mage::getModel('magetsync/shopSection')->toOptionArray(),
        ));

        $shippingTemplate = $fieldsetSS->addField('shipping_template_id', 'select', array(
            'name'  => 'shipping_template_id',
            'label'     => Mage::helper('magetsync')->__("Shipping profile"),
            'class'  => 'validate-select',
            'required' => true,
            'values'    => Mage::getModel('magetsync/shippingTemplate')->toOptionArray(),
        ));

        $valuesAttributeTemplate =  Mage::registry('magetsync_data')->getData();
        $subCategoryAux    = '';
        $subSubCategoryAux = '';
        $subCategoryAux4   = '';
        $subCategoryAux5   = '';
        $subCategoryAux6   = '';
        $subCategoryAux7   = '';
        $style2Aux         = '';

        if($valuesAttributeTemplate['subcategory_id'] == null){ $subCategoryAux = '$(\'subcategory_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subsubcategory_id'] == null){ $subSubCategoryAux = '$(\'subsubcategory_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory4_id'] == null){ $subCategoryAux4 = '$(\'subcategory4_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory5_id'] == null){ $subCategoryAux5 = '$(\'subcategory5_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory6_id'] == null){ $subCategoryAux6 = '$(\'subcategory6_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory7_id'] == null){ $subCategoryAux7 = '$(\'subcategory7_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['style_one'] == null && $valuesAttributeTemplate['style_two'] == null){ $style2Aux = '$(\'style_two\').up(0).up(0).hide();';}

        $shippingTemplate->setAfterElementHtml("<script type=\"text/javascript\">
                    ".$subCategoryAux."
                    ".$subSubCategoryAux."
                    ".$subCategoryAux4."
                    ".$subCategoryAux5."
                    ".$subCategoryAux6."
                    ".$subCategoryAux7."
                    ".$style2Aux."
                </script>");

        if ( Mage::registry('magetsync_data') )
        {
            $form->setValues(Mage::registry('magetsync_data')->getData());
            $form->getElement('is_supply')->setDisabled(false);
            $form->getElement('when_made')->setDisabled(false);
        }


        return parent::_prepareForm();
    }
}