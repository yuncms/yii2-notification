<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\notification\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yuncms\user\models\User;

/**
 * Notice model 通知模型
 *
 * @property integer $id
 * @property integer $user_id 发送方ID，系统消费为空
 * @property integer $to_user_id 接收方ID
 * @property string $type 通知类型代码
 * @property int $source_id 资源ID
 * @property string $subject 资源标题
 * @property string $refer_content 资源内容
 * @property integer $status 状态
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 *
 * @property User $user
 * @property User $toUser
 * @property string $typeText
 */
class Notification extends ActiveRecord
{
    //未读
    const STATUS_UNREAD = 10;
    //已读
    const STATUS_READ = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_UNREAD],
            ['status', 'in', 'range' => [self::STATUS_READ, self::STATUS_UNREAD]],
        ];
    }

    /**
     * 发送者用户实例
     * @return \yii\db\ActiveQueryInterface
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * 接受者用户实例
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }

    /**
     * 获取类型字符
     * @return mixed|null
     */
    public function getTypeText()
    {
        switch ($this->type) {
            case 'follow_user':
                return Yii::t('notification', 'follow on you');
                break;
            case 'answer_question':
                return Yii::t('notification', 'answered the question');
                break;
            case 'follow_question':
                return Yii::t('notification', 'is concerned about the problem');
                break;
            case 'comment_question':
                return Yii::t('notification', 'commented on the question');
                break;
            case 'invite_answer':
                return Yii::t('notification', 'invited you to answer');
                break;
            case 'adopt_answer':
                return Yii::t('notification', 'accepted your answer');
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * 设置指定用户为全部已读
     * @param int $toUserId
     * @return int
     */
    public static function setReadAll($toUserId)
    {
        return self::updateAll(['status' => self::STATUS_READ], ['to_user_id' => $toUserId]);
    }

    /**
     * 快速创建实例
     * @param array $attribute
     * @return mixed
     */
    public static function create(array $attribute)
    {
        $model = new static ($attribute);
        if ($model->save()) {
            return $model;
        }
        return false;
    }
}