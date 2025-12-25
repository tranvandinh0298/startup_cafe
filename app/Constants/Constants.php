<?php

const INGREDIENT_BASE_UNIT = [
    'g',
    'ml',
    'piece',
    'stalk',
    'bottle',
    'pack',
];

const PACKAGE_STATUS = [
    'open',
    'used_up',
    'expired',
    'discarded'
];

const PACKAGE_STATUS_OPEN = PACKAGE_STATUS[0];
const PACKAGE_STATUS_USED_UP = PACKAGE_STATUS[1];
const PACKAGE_STATUS_EXPIRED = PACKAGE_STATUS[2];
const PACKAGE_STATUS_DISCARDED = PACKAGE_STATUS[3];

const RECORD_STATUS = [
    'active',
    'inactive',
];

const RECORD_STATUS_DEFAULT = 'active';

const INVENTORY_ACTION_TYPES = [
    'receive',
    'open',
    'consume',
    'discard',
    'expire',
    'rollback'
];

const INVENTORY_ACTION_TYPE_RECEIVE = INVENTORY_ACTION_TYPES[0];
const INVENTORY_ACTION_TYPE_OPEN = INVENTORY_ACTION_TYPES[1];
const INVENTORY_ACTION_TYPE_CONSUME = INVENTORY_ACTION_TYPES[2];
const INVENTORY_ACTION_TYPE_DISCARD = INVENTORY_ACTION_TYPES[3];
const INVENTORY_ACTION_TYPE_EXPIRE = INVENTORY_ACTION_TYPES[4];
const INVENTORY_ACTION_TYPE_ROLLBACK = INVENTORY_ACTION_TYPES[5];

const ORDER_STATUSES = [
    'draft',
    'confirmed',
    'paid',
    'completed',
    'cancelled'
];

const ORDER_STATUS_DEFAULT = ORDER_STATUSES[0];
const ORDER_STATUS_CONFIRMED = ORDER_STATUSES[1];
const ORDER_STATUS_PAID = ORDER_STATUSES[2];
const ORDER_STATUS_COMPLETED = ORDER_STATUSES[3];
const ORDER_STATUS_CANCELLED = ORDER_STATUSES[4];

const ORDER_FEASIBILITY_OK = 'ok';
const ORDER_FEASIBILITY_IMPOSSIBLE = 'impossible';
const ORDER_FEASIBILITY_REQUIRE_OPEN = 'require_open';
const ORDER_FEASIBILITY_RISKY = 'risky';
