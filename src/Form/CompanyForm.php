<?php
/**
 *
 * part-db version 0.1
 * Copyright (C) 2005 Christoph Lechner
 * http://www.cl-projects.de/
 *
 * part-db version 0.2+
 * Copyright (C) 2009 K. Jacobs and others (see authors.php)
 * http://code.google.com/p/part-db/
 *
 * Part-DB Version 0.4+
 * Copyright (C) 2016 - 2019 Jan Böhmer
 * https://github.com/jbtronics
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 *
 */

namespace App\Form;


use App\Entity\NamedDBElement;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class CompanyForm extends BaseEntityAdminForm
{
    protected function additionalFormElements(FormBuilderInterface $builder, array $options, NamedDBElement $entity)
    {
        $is_new = $entity->getID() === null;

        $builder->add('address', TextareaType::class, ['label' => 'company.address.label',
            'disabled' => !$this->security->isGranted($is_new ? 'create' : 'edit', $entity),
            'attr' => ['placeholder' => 'company.address.placeholder'], 'required' => false,
            'empty_data' => ''
        ]);

        $builder->add('phone_number', TelType::class, ['label' => 'company.phone_number.label',
            'disabled' => !$this->security->isGranted($is_new ? 'create' : 'edit', $entity),
            'attr' => ['placeholder' => 'company.phone_number.placeholder'], 'required' => false,
            'empty_data' => ''
        ]);

        $builder->add('fax_number', TelType::class, ['label' => 'company.fax_number.label',
            'disabled' => !$this->security->isGranted($is_new ? 'create' : 'edit', $entity),
            'attr' => ['placeholder' => 'company.fax_number.placeholder'], 'required' => false,
            'empty_data' => ''
        ]);

        $builder->add('email_address', EmailType::class, ['label' => 'company.email.label',
            'disabled' => !$this->security->isGranted($is_new ? 'create' : 'edit', $entity),
            'attr' => ['placeholder' => 'company.email.placeholder'], 'required' => false,
            'empty_data' => ''
        ]);

        $builder->add('website', UrlType::class, ['label' => 'company.website.label',
            'disabled' => !$this->security->isGranted($is_new ? 'create' : 'edit', $entity),
            'attr' => ['placeholder' => 'company.website.placeholder'], 'required' => false,
            'empty_data' => ''
        ]);

        $builder->add('auto_product_url', UrlType::class, ['label' => 'company.auto_product_url.label',
            'disabled' => !$this->security->isGranted($is_new ? 'create' : 'edit', $entity),
            'attr' => ['placeholder' => 'company.auto_product_url.placeholder'], 'required' => false,
            'empty_data' => ''
        ]);
    }
}