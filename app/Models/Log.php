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

    // Attachments
    public const ACTION_ATTACHMENT_UPLOADED = 38;
    public const ACTION_ATTACHMENT_DELETED = 39;
    public const ACTION_ATTACHMENT_DOWNLOADED = 40;

    // Companies
    public const ACTION_COMPANY_CREATED = 41;
    public const ACTION_COMPANY_UPDATED = 42;
    public const ACTION_COMPANY_DELETED = 43;
    public const ACTION_COMPANY_RESTORED = 44;

    // Contacts
    public const ACTION_CONTACT_CREATED = 45;
    public const ACTION_CONTACT_UPDATED = 46;
    public const ACTION_CONTACT_DELETED = 47;
    public const ACTION_CONTACT_RESTORED = 48;

    // Deals
    public const ACTION_DEAL_CREATED = 49;
    public const ACTION_DEAL_UPDATED = 50;
    public const ACTION_DEAL_DELETED = 51;
    public const ACTION_DEAL_RESTORED = 52;

    // Invoices
    public const ACTION_INVOICE_CREATED = 53;
    public const ACTION_INVOICE_UPDATED = 54;
    public const ACTION_INVOICE_DELETED = 55;
    public const ACTION_INVOICE_RESTORED = 56;
    public const ACTION_INVOICE_SENT = 57;
    public const ACTION_INVOICE_PAID = 58;
    public const ACTION_INVOICE_OVERDUE = 59;

    // Invoice Items
    public const ACTION_INVOICE_ITEM_CREATED = 60;
    public const ACTION_INVOICE_ITEM_UPDATED = 61;
    public const ACTION_INVOICE_ITEM_DELETED = 62;
    public const ACTION_INVOICE_ITEM_RESTORED = 63;

    // Notes
    public const ACTION_NOTE_CREATED = 64;
    public const ACTION_NOTE_UPDATED = 65;
    public const ACTION_NOTE_DELETED = 66;
    public const ACTION_NOTE_RESTORED = 67;

    // Permissions
    public const ACTION_PERMISSION_CREATED = 68;
    public const ACTION_PERMISSION_UPDATED = 69;
    public const ACTION_PERMISSION_DELETED = 70;
    public const ACTION_PERMISSION_RESTORED = 71;

    // Pipelines
    public const ACTION_PIPELINE_CREATED = 72;
    public const ACTION_PIPELINE_UPDATED = 73;
    public const ACTION_PIPELINE_DELETED = 74;
    public const ACTION_PIPELINE_RESTORED = 75;

    // Pipeline Stages
    public const ACTION_PIPELINE_STAGE_CREATED = 76;
    public const ACTION_PIPELINE_STAGE_UPDATED = 77;
    public const ACTION_PIPELINE_STAGE_DELETED = 78;
    public const ACTION_PIPELINE_STAGE_RESTORED = 79;

    // Products
    public const ACTION_PRODUCT_CREATED = 80;
    public const ACTION_PRODUCT_UPDATED = 81;
    public const ACTION_PRODUCT_DELETED = 82;
    public const ACTION_PRODUCT_RESTORED = 83;

    // Roles
    public const ACTION_ROLE_CREATED = 84;
    public const ACTION_ROLE_UPDATED = 85;
    public const ACTION_ROLE_DELETED = 86;
    public const ACTION_ROLE_RESTORED = 87;

    // Tasks
    public const ACTION_TASK_CREATED = 88;
    public const ACTION_TASK_UPDATED = 89;
    public const ACTION_TASK_DELETED = 90;
    public const ACTION_TASK_RESTORED = 91;
    public const ACTION_TASK_COMPLETED = 92;
    public const ACTION_TASK_REOPENED = 93;

    // Users
    public const ACTION_USER_RESTORED = 94;
    public const ACTION_USER_FORCED_DELETED = 95;

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
