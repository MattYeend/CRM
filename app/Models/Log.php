<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    // Action constants
    // Login/Logout
    public const ACTION_LOGIN = 1;
    public const ACTION_LOGOUT = 2;
    public const ACTION_LOGIN_FAILED = 3;
    public const ACTION_LOGIN_PASSWORD_FAILED = 4;
    public const ACTION_LOGIN_EMAIL_FAILED = 5;
    public const ACTION_LOGIN_USERNAME_FAILED = 6;
    public const ACTION_LOGIN_SUCCESS = 7;

    // User Management
    public const ACTION_CREATE_USER = 8;
    public const ACTION_UPDATE_USER = 9;
    public const ACTION_DELETE_USER = 10;
    public const ACTION_SHOW_USER = 11;
    public const ACTION_WELCOME_EMAIL_SENT = 12;
    public const ACTION_CONFIRM_PASSWORD = 13;
    public const ACTION_FORGOT_PASSWORD = 14;
    public const ACTION_REGISTER_USER = 15;
    public const ACTION_RESET_PASSWORD = 16;
    public const ACTION_RESET_EMAIL = 17;
    public const ACTION_RESET_USERNAME = 18;
    public const ACTION_VERIFY_USER = 19;
    public const ACTION_PASSWORD_CHANGED = 20;
    public const ACTION_USER_RESTORED = 21;
    public const ACTION_USER_DELETED = 22;

    // MFA/Settings
    public const ACTION_MFA_ENABLED = 23;
    public const ACTION_MFA_DISABLED = 24;
    public const ACTION_PROFILE_UPDATED = 25;
    public const ACTION_PROFILE_DELETED = 26;
    public const ACTION_EMAIL_UPDATED = 27;

    // Role/Permission Management
    public const ACTION_ROLE_ASSIGNED = 28;
    public const ACTION_PERMISSION_GRANTED = 29;
    public const ACTION_PERMISSION_REVOKED = 30;

    // Errors/Cache
    public const ACTION_GENERAL_ERROR = 31;
    public const ACTION_FOUR_HUNDRED_ERROR = 32;
    public const ACTION_FIVE_HUNDRED_ERRORS = 33;
    public const ACTION_CLEAR_CACHE = 34;

    // Activities
    public const ACTION_ACTIVITY_CREATED = 35;
    public const ACTION_ACTIVITY_UPDATED = 36;
    public const ACTION_ACTIVITY_DELETED = 37;
    public const ACTION_ACTIVITY_COMPLETED = 38;
    public const ACTION_ACTIVITY_REOPENED = 39;
    public const ACTION_ACTIVITY_VIEWED = 40;
    public const ACTION_ACTIVITY_RESTORED = 41;

    // Attachments
    public const ACTION_ATTACHMENT_CREATED = 42;
    public const ACTION_ATTACHMENT_UPDATED = 43;
    public const ACTION_ATTACHMENT_UPLOADED = 44;
    public const ACTION_ATTACHMENT_DELETED = 45;
    public const ACTION_ATTACHMENT_DOWNLOADED = 46;
    public const ACTION_ATTACHMENT_RESTORED = 47;
    public const ACTION_ATTACHMENT_VIEWED = 48;

    // Companies
    public const ACTION_COMPANY_CREATED = 49;
    public const ACTION_COMPANY_UPDATED = 50;
    public const ACTION_COMPANY_DELETED = 51;
    public const ACTION_COMPANY_RESTORED = 52;
    public const ACTION_COMPANY_VIEWED = 53;

    // Deals
    public const ACTION_DEAL_CREATED = 54;
    public const ACTION_DEAL_UPDATED = 55;
    public const ACTION_DEAL_DELETED = 56;
    public const ACTION_DEAL_RESTORED = 57;
    public const ACTION_DEAL_VIEWED = 58;

    // Invoices
    public const ACTION_INVOICE_CREATED = 59;
    public const ACTION_INVOICE_UPDATED = 60;
    public const ACTION_INVOICE_DELETED = 61;
    public const ACTION_INVOICE_RESTORED = 62;
    public const ACTION_INVOICE_SENT = 63;
    public const ACTION_INVOICE_PAID = 64;
    public const ACTION_INVOICE_OVERDUE = 65;

    // Invoice Items
    public const ACTION_INVOICE_ITEM_CREATED = 66;
    public const ACTION_INVOICE_ITEM_UPDATED = 67;
    public const ACTION_INVOICE_ITEM_DELETED = 68;
    public const ACTION_INVOICE_ITEM_RESTORED = 69;
    public const ACTION_INVOICE_ITEM_VIEWED = 70;

    // Job Titles
    public const ACTION_JOB_TITLE_CREATED = 71;
    public const ACTION_JOB_TITLE_UPDATED = 72;
    public const ACTION_JOB_TITLE_DELETED = 73;
    public const ACTION_JOB_TITLE_RESTORED = 74;
    public const ACTION_JOB_TITLE_VIEWED = 75;

    // Leads
    public const ACTION_LEAD_CREATED = 76;
    public const ACTION_LEAD_UPDATED = 77;
    public const ACTION_LEAD_DELETED = 78;
    public const ACTION_LEAD_RESTORED = 79;
    public const ACTION_LEAD_CONVERTED = 80;
    public const ACTION_LEAD_UNCONVERTED = 81;
    public const ACTION_LEAD_VIEWED = 82;
    public const ACTION_LEAD_ASSIGNED = 83;
    public const ACTION_LEAD_UNASSIGNED = 84;

    // Learning Material
    public const ACTION_LEARNING_CREATED = 85;
    public const ACTION_LEARNING_UPDATED = 86;
    public const ACTION_LEARNING_DELETED = 87;
    public const ACTION_LEARNING_RESTORED = 88;
    public const ACTION_LEARNING_VIEWED = 89;
    public const ACTION_LEARNING_COMPLETED = 90;
    public const ACTION_LEARNING_INCOMPLETE = 91;

    // Notes
    public const ACTION_NOTE_CREATED = 92;
    public const ACTION_NOTE_UPDATED = 93;
    public const ACTION_NOTE_DELETED = 94;
    public const ACTION_NOTE_RESTORED = 95;
    public const ACTION_NOTE_VIEWED = 96;

    // Orders
    public const ACTION_ORDER_CREATED = 97;
    public const ACTION_ORDER_UPDATED = 98;
    public const ACTION_ORDER_DELETED = 99;
    public const ACTION_ORDER_RESTORED = 100;
    public const ACTION_ORDER_VIEWED = 101;

    // Permissions
    public const ACTION_PERMISSION_CREATED = 102;
    public const ACTION_PERMISSION_UPDATED = 103;
    public const ACTION_PERMISSION_DELETED = 104;
    public const ACTION_PERMISSION_RESTORED = 105;
    public const ACTION_PERMISSION_VIEWED = 106;

    // Pipelines
    public const ACTION_PIPELINE_CREATED = 107;
    public const ACTION_PIPELINE_UPDATED = 108;
    public const ACTION_PIPELINE_DELETED = 109;
    public const ACTION_PIPELINE_RESTORED = 110;
    public const ACTION_PIPELINE_VIEWED = 111;

    // Pipeline Stages
    public const ACTION_PIPELINE_STAGE_CREATED = 112;
    public const ACTION_PIPELINE_STAGE_UPDATED = 113;
    public const ACTION_PIPELINE_STAGE_DELETED = 114;
    public const ACTION_PIPELINE_STAGE_RESTORED = 115;
    public const ACTION_PIPELINE_STAGE_VIEWED = 116;

    // Products
    public const ACTION_PRODUCT_CREATED = 117;
    public const ACTION_PRODUCT_UPDATED = 118;
    public const ACTION_PRODUCT_DELETED = 119;
    public const ACTION_PRODUCT_RESTORED = 120;
    public const ACTION_PRODUCT_VIEWED = 121;

    // Quotes
    public const ACTION_QUOTE_CREATED = 122;
    public const ACTION_QUOTE_UPDATED = 123;
    public const ACTION_QUOTE_DELETED = 124;
    public const ACTION_QUOTE_RESTORED = 125;
    public const ACTION_QUOTE_VIEWED = 126;

    // Roles
    public const ACTION_ROLE_CREATED = 127;
    public const ACTION_ROLE_UPDATED = 128;
    public const ACTION_ROLE_DELETED = 129;
    public const ACTION_ROLE_RESTORED = 130;
    public const ACTION_ROLE_VIEWED = 131;

    // Tasks
    public const ACTION_TASK_CREATED = 132;
    public const ACTION_TASK_UPDATED = 133;
    public const ACTION_TASK_DELETED = 134;
    public const ACTION_TASK_RESTORED = 135;
    public const ACTION_TASK_COMPLETED = 136;
    public const ACTION_TASK_REOPENED = 137;
    public const ACTION_TASK_VIEWED = 138;

    // Parts
    public const ACTION_PART_CREATED = 139;
    public const ACTION_PART_UPDATED = 140;
    public const ACTION_PART_DELETED = 141;
    public const ACTION_PART_RESTORED = 142;
    public const ACTION_PART_VIEWED = 143;

    // Suppliers
    public const ACTION_SUPPLIER_CREATED = 144;
    public const ACTION_SUPPLIER_UPDATED = 145;
    public const ACTION_SUPPLIER_DELETED = 146;
    public const ACTION_SUPPLIER_RESTORED = 147;
    public const ACTION_SUPPLIER_VIEWED = 148;

    // Part Categories
    public const ACTION_PART_CATEGORY_CREATED = 149;
    public const ACTION_PART_CATEGORY_UPDATED = 150;
    public const ACTION_PART_CATEGORY_DELETED = 151;
    public const ACTION_PART_CATEGORY_RESTORED = 152;
    public const ACTION_PART_CATEGORY_VIEWED = 153;

    // Part Images
    public const ACTION_PART_IMAGE_CREATED = 154;
    public const ACTION_PART_IMAGE_UPDATED = 155;
    public const ACTION_PART_IMAGE_DELETED = 156;
    public const ACTION_PART_IMAGE_RESTORED = 157;
    public const ACTION_PART_IMAGE_VIEWED = 158;

    // New Logging Actions should go here to be reviewed
    // by the development team for future releases.
    // Ensure to update the documentation accordingly.

    // Empty constants
    public const ACTION_NONE = 000;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'action_id',
        'data',
        'logged_in_user_id',
        'related_to_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo<User,Log>
     */
    public function loggedInUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_in_user_id');
    }

    /**
     * Get the user related to the action, if applicable.
     *
     * @return BelongsTo<User,Log>
     */
    public function relatedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_to_user_id');
    }

    /**
     * Log an action.
     *
     * @param int $action The action constant.
     *
     * @param array|null $data Additional data related to the action.
     *
     * @param int|null $logged_in_user_id The ID of the user performing
     * the action.
     *
     * @param int|null $related_to_user_id The ID of the user related
     * to the action.
     */
    public static function log(
        $action = 0,
        $data = null,
        $logged_in_user_id = null,
        $related_to_user_id = null
    ) {
        if (isset($action)) {
            $logged_in_user_id = $logged_in_user_id ?? Auth::id();

            if (! is_null($data) && ! is_array($data)) {
                throw new \InvalidArgumentException(
                    'Data must be an array or null.'
                );
            }

            $log = new self();
            $log->logged_in_user_id = $logged_in_user_id;
            $log->action_id = $action;
            $log->related_to_user_id = $related_to_user_id;
            $log->data = $data;
            $log->save();
        }
    }
}
