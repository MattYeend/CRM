<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    // Action constants
    // Login/Logout
    public const ACTION_LOGIN = 1;
    public const ACTION_LOGOUT = 2;

    // User Management
    public const ACTION_CREATE_USER = 3;
    public const ACTION_UPDATE_USER = 4;
    public const ACTION_DELETE_USER = 5;
    public const ACTION_SHOW_USER = 6;
    public const ACTION_WELCOME_EMAIL_SENT = 7;
    public const ACTION_CONFIRM_PASSWORD = 8;
    public const ACTION_FORGOT_PASSWORD = 9;
    public const ACTION_REGISTER_USER = 10;
    public const ACTION_LOGIN_FAILED = 11;
    public const ACTION_LOGIN_PASSWORD_FAILED = 12;
    public const ACTION_LOGIN_EMAIL_FAILED = 13;
    public const ACTION_LOGIN_USERNAME_FAILED = 14;
    public const ACTION_LOGIN_SUCCESS = 15;
    public const ACTION_RESET_PASSWORD = 16;
    public const ACTION_RESET_EMAIL = 17;
    public const ACTION_RESET_USERNAME = 18;
    public const ACTION_VERIFY_USER = 19;
    public const ACTION_PASSWORD_CHANGED = 20;
    public const ACTION_USER_RESTORED = 21;
    public const ACTION_USER_DELETED = 22;
    public const ACTION_USER_FORCED_DELETED = 23;

    // MFA/Settings
    public const ACTION_MFA_ENABLED = 24;
    public const ACTION_MFA_DISABLED = 25;
    public const ACTION_PROFILE_UPDATED = 26;
    public const ACTION_PROFILE_DELETED = 27;
    public const ACTION_EMAIL_UPDATED = 28;

    // Role/Permission Management
    public const ACTION_ROLE_ASSIGNED = 29;
    public const ACTION_PERMISSION_GRANTED = 30;
    public const ACTION_PERMISSION_REVOKED = 31;

    // Errors/Cache
    public const ACTION_GENERAL_ERROR = 32;
    public const ACTION_FOUR_HUNDRED_ERROR = 33;
    public const ACTION_FIVE_HUNDRED_ERRORS = 34;
    public const ACTION_CLEAR_CACHE = 35;

    // Activities
    public const ACTION_ACTIVITY_CREATED = 36;
    public const ACTION_ACTIVITY_UPDATED = 37;
    public const ACTION_ACTIVITY_DELETED = 38;
    public const ACTION_ACTIVITY_COMPLETED = 39;
    public const ACTION_ACTIVITY_REOPENED = 40;
    public const ACTION_ACTIVITY_VIEWED = 41;
    public const ACTION_ACTIVIED_RESTORED = 42;

    // Attachments
    public const ACTION_ATTACHMENT_CREATED = 43;
    public const ACTION_ATTACHMENT_UPDATED = 44;
    public const ACTION_ATTACHMENT_UPLOADED = 45;
    public const ACTION_ATTACHMENT_DELETED = 46;
    public const ACTION_ATTACHMENT_DOWNLOADED = 47;
    public const ACTION_ATTACHMENT_RESTORED = 48;
    public const ACTION_ATTACHMENT_VIEWED = 49;

    // Companies
    public const ACTION_COMPANY_CREATED = 50;
    public const ACTION_COMPANY_UPDATED = 51;
    public const ACTION_COMPANY_DELETED = 52;
    public const ACTION_COMPANY_RESTORED = 53;
    public const ACTION_COMPANY_VIEWED = 54;

    // Contacts
    public const ACTION_CONTACT_CREATED = 55;
    public const ACTION_CONTACT_UPDATED = 56;
    public const ACTION_CONTACT_DELETED = 57;
    public const ACTION_CONTACT_RESTORED = 58;
    public const ACTION_CONTACT_VIEWED = 59;

    // Deals
    public const ACTION_DEAL_CREATED = 60;
    public const ACTION_DEAL_UPDATED = 61;
    public const ACTION_DEAL_DELETED = 62;
    public const ACTION_DEAL_RESTORED = 63;
    public const ACTION_DEAL_VIEWED = 64;

    // Invoices
    public const ACTION_INVOICE_CREATED = 65;
    public const ACTION_INVOICE_UPDATED = 66;
    public const ACTION_INVOICE_DELETED = 67;
    public const ACTION_INVOICE_RESTORED = 68;
    public const ACTION_INVOICE_SENT = 69;
    public const ACTION_INVOICE_PAID = 70;
    public const ACTION_INVOICE_OVERDUE = 71;

    // Invoice Items
    public const ACTION_INVOICE_ITEM_CREATED = 72;
    public const ACTION_INVOICE_ITEM_UPDATED = 73;
    public const ACTION_INVOICE_ITEM_DELETED = 74;
    public const ACTION_INVOICE_ITEM_RESTORED = 75;
    public const ACTION_INVOICE_ITEM_VIEWED = 76;

    // Notes
    public const ACTION_NOTE_CREATED = 77;
    public const ACTION_NOTE_UPDATED = 78;
    public const ACTION_NOTE_DELETED = 79;
    public const ACTION_NOTE_RESTORED = 80;
    public const ACTION_NOTE_VIEWED = 81;

    // Permissions
    public const ACTION_PERMISSION_CREATED = 82;
    public const ACTION_PERMISSION_UPDATED = 83;
    public const ACTION_PERMISSION_DELETED = 84;
    public const ACTION_PERMISSION_RESTORED = 85;
    public const ACTION_PERMISSION_VIEWED = 86;

    // Pipelines
    public const ACTION_PIPELINE_CREATED = 87;
    public const ACTION_PIPELINE_UPDATED = 88;
    public const ACTION_PIPELINE_DELETED = 89;
    public const ACTION_PIPELINE_RESTORED = 90;
    public const ACTION_PIPELINE_VIEWED = 91;

    // Pipeline Stages
    public const ACTION_PIPELINE_STAGE_CREATED = 92;
    public const ACTION_PIPELINE_STAGE_UPDATED = 93;
    public const ACTION_PIPELINE_STAGE_DELETED = 94;
    public const ACTION_PIPELINE_STAGE_RESTORED = 95;
    public const ACTION_PIPELINE_STAGE_VIEWED = 96;

    // Products
    public const ACTION_PRODUCT_CREATED = 97;
    public const ACTION_PRODUCT_UPDATED = 98;
    public const ACTION_PRODUCT_DELETED = 99;
    public const ACTION_PRODUCT_RESTORED = 100;
    public const ACTION_PRODUCT_VIEWED = 101;

    // Roles
    public const ACTION_ROLE_CREATED = 102;
    public const ACTION_ROLE_UPDATED = 103;
    public const ACTION_ROLE_DELETED = 104;
    public const ACTION_ROLE_RESTORED = 105;
    public const ACTION_ROLE_VIEWED = 106;

    // Tasks
    public const ACTION_TASK_CREATED = 107;
    public const ACTION_TASK_UPDATED = 108;
    public const ACTION_TASK_DELETED = 109;
    public const ACTION_TASK_RESTORED = 110;
    public const ACTION_TASK_COMPLETED = 111;
    public const ACTION_TASK_REOPENED = 112;
    public const ACTION_TASK_VIEWED = 113;

    // Learning Material
    public const ACTION_LEARNING_CREATED = 114;
    public const ACTION_LEARNING_UPDATED = 115;
    public const ACTION_LEARNING_DELETED = 116;
    public const ACTION_LEARNING_RESTORED = 117;
    public const ACTION_LEARNING_VIEWED = 118;
    public const ACTION_LEARNING_COMPLETED = 119;
    public const ACTION_LEARNING_INCOMPLETE = 120;

    // Leads
    public const ACTION_LEAD_CREATED = 121;
    public const ACTION_LEAD_UPDATED = 122;
    public const ACTION_LEAD_DELETED = 123;
    public const ACTION_LEAD_RESTORED = 124;
    public const ACTION_LEAD_CONVERTED = 125;
    public const ACTION_LEAD_UNCONVERTED = 126;
    public const ACTION_LEAD_VIEWED = 127;
    public const ACTION_LEAD_ASSIGNED = 128;
    public const ACTION_LEAD_UNASSIGNED = 129;

    // Orders
    public const ACTION_ORDER_CREATED = 130;
    public const ACTION_ORDER_UPDATED = 131;
    public const ACTION_ORDER_DELETED = 132;
    public const ACTION_ORDER_RESTORED = 133;
    public const ACTION_ORDER_VIEWED = 134;

    // Quotes
    public const ACTION_QUOTE_CREATED = 135;
    public const ACTION_QUOTE_UPDATED = 136;
    public const ACTION_QUOTE_DELETED = 137;
    public const ACTION_QUOTE_RESTORED = 138;
    public const ACTION_QUOTE_VIEWED = 139;

    // Job Titles
    public const ACTION_JOB_TITLE_CREATED = 140;
    public const ACTION_JOB_TITLE_UPDATED = 141;
    public const ACTION_JOB_TITLE_DELETED = 142;
    public const ACTION_JOB_TITLE_RESTORED = 143;
    public const ACTION_JOB_TITLE_VIEWED = 144;

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
     * @var array<int, string>
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
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loggedInUser()
    {
        return $this->belongsTo(User::class, 'logged_in_user_id');
    }

    /**
     * Get the user related to the action, if applicable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relatedToUser()
    {
        return $this->belongsTo(User::class, 'related_to_user_id');
    }

    /**
     * Log an action.
     *
     * @param int $action The action constant.
     * @param array|null $data Additional data related to the action.
     * @param int|null $logged_in_user_id The ID of the user performing
     * the action.
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
