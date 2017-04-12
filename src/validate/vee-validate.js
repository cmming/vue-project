import Vue from 'vue'
import VeeValidate , { Validator } from 'vee-validate';


var zh_CN = {
  after: (field, [target]) => ` 必须在${target}之后`,
  alpha_dash: (field) => ` 能够包含字母数字字符，包括破折号、下划线`,
  alpha_num: (field) => ` 只能包含字母数字字符.`,
  alpha_spaces: (field) => `  只能包含字母字符，包括空格.`,
  alpha: (field) => `  只能包含字母字符.`,
  before: (field, [target]) => `  必须在${target} 之前.`,
  between: (field, [min, max]) => `  必须在${min} ${max}之间.`,
  confirmed: (field, [confirmedField]) => `  不能和${confirmedField}匹配.`,
  date_between: (field, [min, max]) => ` 必须在${min}和${max}之间.`,
  date_format: (field, [format]) => ` 必须在在${format}格式中.`,
  decimal: (field, [decimals] = ['*']) => `  必须是数字的而且能够包含${decimals === '*' ? '' : decimals} 小数点.`,
  digits: (field, [length]) => ` 必须是数字，且精确到 ${length}数`,
  dimensions: (field, [width, height]) => ` 必须是 ${width} 像素到 ${height} 像素.`,
  email: (field) => `  必须是有效的邮箱.`,
  ext: (field) => `  必须是有效的文件.`,
  image: (field) => `  必须是图片.`,
  in: (field) => `  必须是一个有效值.`,
  ip: (field) => `  必须是一个有效的地址.`,
  max: (field, [length]) => `  不能大于${length}字符.`,
  mimes: (field) => `  必须是有效的文件类型.`,
  min: (field, [length]) => `  必须至少有 ${length} 字符.`,
  not_in: (field) => ` 必须是一个有效值.`,
  numeric: (field) => ` 只能包含数字字符.`,
  regex: (field) => ` 格式无效.`,
  required: (field) => `不能为空.`,
  size: (field, [size]) => ` 必须小于 ${size} KB.`,
  url: (field) => ` 不是有效的url.`
};
// // 自定义规则
const isMobile = {
    messages: {
        zh_CN:(field, args) => '必须是11位手机号码',
    },
    validate: (value, args) => {
       return value.length == 11 && /^((13|14|15|17|18)[0-9]{1}\d{8})$/.test(value)
    }
}
Validator.extend('mobile', isMobile);


const userName = {
    messages: {
        zh_CN:(field, args) => '最长不超过16个中文',
    },
    validate: (value, args) => {
       return /^[\u4e00-\u9fa5]{0,16}$/.test(value)
    }
}
Validator.extend('userName', userName);

Vue.use(VeeValidate, {
  locale: 'zh_CN',
  dictionary: {
    zh_CN: {
      messages: zh_CN
    }
  }
});

// Vue.use(VeeValidate);
