<?php
/**
 * Copyright 2016 Henrik Hedelund
 *
 * This file is part of Henhed_Piwik.
 *
 * Henhed_Piwik is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Henhed_Piwik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Henhed_Piwik.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Henhed\Piwik\CustomVariable;

/**
 * Customer group custom variable
 *
 */
class GroupVariable extends AbstractCustomVariable
{

    /**
     * {@inheritDoc}
     */
    const NAME = 'Group';

    /**
     * Customer group repository
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface $_groupRepository
     */
    protected $_groupRepository;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->_groupRepository = $groupRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(Context $context, $hint = self::VALUE_HINT_ID)
    {
        $id = $context->getCustomerGroupId();
        if ($hint == self::VALUE_HINT_ID) {
            return (string) $id;
        }

        try {
            return $this->_groupRepository->getById($id)->getCode();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }
}
