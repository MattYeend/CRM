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

    // MFA/Settings
    public const ACTION_MFA_ENABLED = 21;
    public const ACTION_MFA_DISABLED = 22;
    public const ACTION_PROFILE_UPDATED = 23;
    public const ACTION_PROFILE_DELETED = 24;
    public const ACTION_EMAIL_UPDATED = 25;

    // Role/Permission Management
    public const ACTION_ROLE_ASSIGNED = 26;
    public const ACTION_PERMISSION_GRANTED = 27;
    public const ACTION_PERMISSION_REVOKED = 28;

    // Errors/Cache
    public const ACTION_GENERAL_ERROR = 29;
    public const ACTION_FOUR_HUNDRED_ERROR = 30;
    public const ACTION_FIVE_HUNDRED_ERRORS = 31;
    public const ACTION_CLEAR_CACHE = 32;

    // Activities
    public const ACTION_ACTIVITY_CREATED = 33;
    public const ACTION_ACTIVITY_UPDATED = 34;
    public const ACTION_ACTIVITY_DELETED = 35;
    public const ACTION_ACTIVITY_COMPLETED = 36;
    public const ACTION_ACTIVITY_REOPENED = 37;
    public const ACTION_ACTIVITY_VIEWED = 38;

    // Attachments
    public const ACTION_ATTACHMENT_CREATED = 39;
    public const ACTION_ATTACHMENT_UPDATED = 40;
    public const ACTION_ATTACHMENT_UPLOADED = 41;
    public const ACTION_ATTACHMENT_DELETED = 42;
    public const ACTION_ATTACHMENT_DOWNLOADED = 43;
    public const ACTION_ATTACHMENT_RESTORED = 44;
    public const ACTION_ATTACHMENT_VIEWED = 45;

    // Companies
    public const ACTION_COMPANY_CREATED = 46;
    public const ACTION_COMPANY_UPDATED = 47;
    public const ACTION_COMPANY_DELETED = 48;
    public const ACTION_COMPANY_RESTORED = 49;
    public const ACTION_COMPANY_VIEWED = 50;

    // Contacts
    public const ACTION_CONTACT_CREATED = 51;
    public const ACTION_CONTACT_UPDATED = 52;
    public const ACTION_CONTACT_DELETED = 53;
    public const ACTION_CONTACT_RESTORED = 54;
    public const ACTION_CONTACT_VIEWED = 55;

    // Deals
    public const ACTION_DEAL_CREATED = 56;
    public const ACTION_DEAL_UPDATED = 57;
    public const ACTION_DEAL_DELETED = 58;
    public const ACTION_DEAL_RESTORED = 59;
    public const ACTION_DEAL_VIEWED = 60;

    // Invoices
    public const ACTION_INVOICE_CREATED = 61;
    public const ACTION_INVOICE_UPDATED = 62;
    public const ACTION_INVOICE_DELETED = 63;
    public const ACTION_INVOICE_RESTORED = 64;
    public const ACTION_INVOICE_SENT = 65;
    public const ACTION_INVOICE_PAID = 66;
    public const ACTION_INVOICE_OVERDUE = 67;

    // Invoice Items
    public const ACTION_INVOICE_ITEM_CREATED = 68;
    public const ACTION_INVOICE_ITEM_UPDATED = 69;
    public const ACTION_INVOICE_ITEM_DELETED = 70;
    public const ACTION_INVOICE_ITEM_RESTORED = 71;
    public const ACTION_INVOICE_ITEM_VIEWED = 72;

    // Notes
    public const ACTION_NOTE_CREATED = 73;
    public const ACTION_NOTE_UPDATED = 74;
    public const ACTION_NOTE_DELETED = 75;
    public const ACTION_NOTE_RESTORED = 76;
    public const ACTION_NOTE_VIEWED = 77;

    // Permissions
    public const ACTION_PERMISSION_CREATED = 78;
    public const ACTION_PERMISSION_UPDATED = 79;
    public const ACTION_PERMISSION_DELETED = 80;
    public const ACTION_PERMISSION_RESTORED = 81;
    public const ACTION_PERMISSION_VIEWED = 82;

    // Pipelines
    public const ACTION_PIPELINE_CREATED = 83;
    public const ACTION_PIPELINE_UPDATED = 84;
    public const ACTION_PIPELINE_DELETED = 85;
    public const ACTION_PIPELINE_RESTORED = 86;
    public const ACTION_PIPELINE_VIEWED = 87;

    // Pipeline Stages
    public const ACTION_PIPELINE_STAGE_CREATED = 88;
    public const ACTION_PIPELINE_STAGE_UPDATED = 89;
    public const ACTION_PIPELINE_STAGE_DELETED = 90;
    public const ACTION_PIPELINE_STAGE_RESTORED = 91;
    public const ACTION_PIPELINE_STAGE_VIEWED = 92;

    // Products
    public const ACTION_PRODUCT_CREATED = 93;
    public const ACTION_PRODUCT_UPDATED = 94;
    public const ACTION_PRODUCT_DELETED = 95;
    public const ACTION_PRODUCT_RESTORED = 96;
    public const ACTION_PRODUCT_VIEWED = 97;

    // Roles
    public const ACTION_ROLE_CREATED = 98;
    public const ACTION_ROLE_UPDATED = 99;
    public const ACTION_ROLE_DELETED = 100;
    public const ACTION_ROLE_RESTORED = 101;
    public const ACTION_ROLE_VIEWED = 102;

    // Tasks
    public const ACTION_TASK_CREATED = 103;
    public const ACTION_TASK_UPDATED = 104;
    public const ACTION_TASK_DELETED = 105;
    public const ACTION_TASK_RESTORED = 106;
    public const ACTION_TASK_COMPLETED = 107;
    public const ACTION_TASK_REOPENED = 108;
    public const ACTION_TASK_VIEWED = 109;

    // Users
    public const ACTION_USER_RESTORED = 110;
    public const ACTION_USER_FORCED_DELETED = 111;

    // Learning Material
    public const ACTION_LEARNING_CREATED = 112;
    public const ACTION_LEARNING_UPDATED = 113;
    public const ACTION_LEARNING_DELETED = 114;
    public const ACTION_LEARNING_RESTORED = 115;
    public const ACTION_LEARNING_VIEWED = 116;
    public const ACTION_LEARNING_COMPLETED = 117;
    public const ACTION_LEARNING_INCOMPLETE = 118;
    public const ACTION_LEARNING_ARCHIVED = 119;
    public const ACTION_LEARNING_UNARCHIVED = 120;

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
    public const ACTION_LEAD_ARCHIVED = 130;
    public const ACTION_LEAD_UNARCHIVED = 131;

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
