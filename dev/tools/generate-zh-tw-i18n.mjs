import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const themeRoot = path.resolve(__dirname, '../..');
const languagesDir = path.join(themeRoot, 'languages');
const sourcePo = path.join(languagesDir, 'developer-starter-zh_CN.po');
const targetPo = path.join(languagesDir, 'developer-starter-zh_TW.po');

const phrasePairs = [
  ['启灵', '啟靈'],
  ['简体中文', '簡體中文'],
  ['繁体中文', '繁體中文'],
  ['主题设置', '主題設定'],
  ['设置', '設定'],
  ['默认', '預設'],
  ['页面', '頁面'],
  ['模板', '範本'],
  ['模块', '模組'],
  ['数据包', '資料包'],
  ['数据', '資料'],
  ['文件', '檔案'],
  ['上传', '上傳'],
  ['下载', '下載'],
  ['用户', '使用者'],
  ['账号', '帳號'],
  ['帐户', '帳戶'],
  ['登录', '登入'],
  ['注册', '註冊'],
  ['注销', '登出'],
  ['登出', '登出'],
  ['邮箱', '信箱'],
  ['邮件', '郵件'],
  ['短信', '簡訊'],
  ['手机号', '手機號碼'],
  ['手机', '手機'],
  ['链接', '連結'],
  ['菜单', '選單'],
  ['导航', '導覽'],
  ['博客', '部落格'],
  ['软件', '軟體'],
  ['插件', '外掛'],
  ['网络', '網路'],
  ['服务器', '伺服器'],
  ['视频', '影片'],
  ['音频', '音訊'],
  ['屏幕', '螢幕'],
  ['图片', '圖片'],
  ['图像', '圖像'],
  ['头像', '頭像'],
  ['二维码', '二維碼'],
  ['优化', '最佳化'],
  ['检测', '偵測'],
  ['查看', '檢視'],
  ['收藏', '收藏'],
  ['点赞', '按讚'],
  ['发布', '發佈'],
  ['文章', '文章'],
  ['内容', '內容'],
  ['评论', '評論'],
  ['回复', '回覆'],
  ['分类', '分類'],
  ['标签', '標籤'],
  ['首页', '首頁'],
  ['归档', '彙整'],
  ['面包屑', '麵包屑'],
  ['侧边栏', '側邊欄'],
  ['页脚', '頁尾'],
  ['头部', '頁首'],
  ['后台', '後台'],
  ['前台', '前台'],
  ['站点', '網站'],
  ['网站', '網站'],
  ['访客', '訪客'],
  ['游客', '訪客'],
  ['网址', '網址'],
  ['地址', '位址'],
  ['联系', '聯絡'],
  ['联系我们', '聯絡我們'],
  ['关于我们', '關於我們'],
  ['服务', '服務'],
  ['产品', '產品'],
  ['案例', '案例'],
  ['新闻', '新聞'],
  ['公告', '公告'],
  ['招聘', '招募'],
  ['职位', '職缺'],
  ['申请', '申請'],
  ['简历', '履歷'],
  ['订单', '訂單'],
  ['商城', '商店'],
  ['商品', '商品'],
  ['商店', '商店'],
  ['购物车', '購物車'],
  ['结账', '結帳'],
  ['支付', '付款'],
  ['发票', '發票'],
  ['价格', '價格'],
  ['授权', '授權'],
  ['许可', '授權'],
  ['订阅', '訂閱'],
  ['免费', '免費'],
  ['收费', '收費'],
  ['技术支持', '技術支援'],
  ['支持', '支援'],
  ['浏览', '瀏覽'],
  ['浏览器', '瀏覽器'],
  ['搜索', '搜尋'],
  ['筛选', '篩選'],
  ['排序', '排序'],
  ['重置', '重設'],
  ['保存', '儲存'],
  ['更新', '更新'],
  ['编辑', '編輯'],
  ['删除', '刪除'],
  ['清理', '清理'],
  ['恢复', '還原'],
  ['导入', '匯入'],
  ['导出', '匯出'],
  ['备份', '備份'],
  ['草稿', '草稿'],
  ['发布', '發佈'],
  ['累计', '累計'],
  ['累积', '累積'],
  ['预览', '預覽'],
  ['临时', '暫時'],
  ['当前', '目前'],
  ['开启', '開啟'],
  ['关闭', '關閉'],
  ['启用', '啟用'],
  ['禁用', '停用'],
  ['错误', '錯誤'],
  ['失败', '失敗'],
  ['成功', '成功'],
  ['警告', '警告'],
  ['风险', '風險'],
  ['安全', '安全'],
  ['权限', '權限'],
  ['验证码', '驗證碼'],
  ['实名认证', '實名認證'],
  ['隐私', '隱私'],
  ['策略', '策略'],
  ['协议', '協議'],
  ['协议版本', '協議版本'],
  ['响应头', '回應標頭'],
  ['请求', '請求'],
  ['响应', '回應'],
  ['缓存', '快取'],
  ['加载', '載入'],
  ['加载更多', '載入更多'],
  ['正在加载', '正在載入'],
  ['跳转', '跳轉'],
  ['访问', '存取'],
  ['无法', '無法'],
  ['请先', '請先'],
  ['必须', '必須'],
  ['暂时', '暫時'],
  ['以后', '之後'],
  ['后续', '後續'],
  ['这里', '這裡'],
  ['这个', '這個'],
  ['这些', '這些'],
  ['这样', '這樣'],
  ['没有', '沒有'],
  ['是否', '是否'],
  ['选择', '選擇'],
  ['语言', '語言'],
  ['中文', '中文'],
  ['英文', '英文'],
  ['日文', '日文'],
  ['韩文', '韓文'],
  ['法文', '法文'],
  ['德文', '德文'],
  ['西班牙文', '西班牙文'],
  ['俄文', '俄文'],
  ['开发', '開發'],
  ['开发者', '開發者'],
  ['企业', '企業'],
  ['公司', '公司'],
  ['专业', '專業'],
  ['业务', '業務'],
  ['行业', '產業'],
  ['设计', '設計'],
  ['系统', '系統'],
  ['信息', '資訊'],
  ['功能', '功能'],
  ['说明', '說明'],
  ['帮助', '說明'],
  ['文档', '文件'],
  ['知识库', '知識庫'],
  ['资源', '資源'],
  ['工具', '工具'],
  ['接口', '介面'],
  ['界面', '介面'],
  ['本地', '本機'],
  ['多语言', '多語言'],
  ['机器翻译', '機器翻譯'],
  ['机翻', '機器翻譯'],
  ['简码', '簡碼'],
  ['语言包', '語言包'],
  ['兜底', '備用'],
  ['字段', '欄位'],
  ['表单', '表單'],
  ['表格', '表格'],
  ['关系', '關係'],
  ['计划', '計劃'],
  ['规划', '規劃'],
  ['数量', '數量'],
  ['状态', '狀態'],
  ['类型', '類型'],
  ['标题', '標題'],
  ['副标题', '副標題'],
  ['名称', '名稱'],
  ['昵称', '暱稱'],
  ['描述', '描述'],
  ['关键词', '關鍵字'],
  ['关键词', '關鍵詞'],
  ['颜色', '顏色'],
  ['背景', '背景'],
  ['间距', '間距'],
  ['宽度', '寬度'],
  ['高度', '高度'],
  ['尺寸', '尺寸'],
  ['布局', '版面配置'],
  ['样式', '樣式'],
  ['全局', '全域'],
  ['正文', '內文'],
  ['内存', '記憶體'],
  ['统计', '統計'],
  ['历史', '歷史'],
  ['记录', '紀錄'],
  ['日志', '日誌'],
  ['目录', '目錄'],
  ['路径', '路徑'],
  ['队列', '佇列'],
  ['干净', '乾淨'],
  ['顶部', '頂部'],
  ['顶层', '頂層'],
  ['顺序', '順序'],
  ['尽量', '盡量'],
  ['小时', '小時'],
  ['分钟', '分鐘'],
  ['秒钟', '秒鐘'],
  ['工作日', '工作日'],
  ['二维码', '二維碼'],
];

const charPairs = `
万萬 与與 丑醜 专專 业業 丛叢 东東 丝絲 丢丟 两兩 严嚴 丧喪 个個 丰豐 临臨 为為 丽麗 举舉 么麼 义義 乌烏 乐樂 乔喬 习習 乡鄉 书書 买買 乱亂 争爭 于於 亏虧 云雲 亚亞 产產 亩畝 亲親 亵褻 亸嚲 亿億 仅僅 从從 仑侖 仓倉 仪儀 们們 价價 众眾 优優 会會 伛傴 伞傘 伟偉 传傳 伤傷 伦倫 伪偽 伫佇 体體 余餘 佣傭 佥僉 侠俠 侣侶 侥僥 侦偵 侧側 侨僑 侩儈 侪儕 侬儂 俣俁 俦儔 俨儼 俩倆 俪儷 俭儉 债債 倾傾 偿償 傥儻 傧儐 储儲 傩儺 儿兒 兑兌 兖兗 兰蘭 关關 兴興 兹茲 养養 兽獸 冁囅 内內 冈岡 册冊 写寫 军軍 农農 冯馮 冲沖 决決 况況 冻凍 净淨 凉涼 减減 凑湊 凛凜 几幾 凤鳳 凭憑 凯凱 击擊 凿鑿 刍芻 刘劉 则則 刚剛 创創 删刪 别別 刬剗 刭剄 刹剎 刽劊 刾刺 剀剴 剂劑 剐剮 剑劍 剧劇 劝勸 办辦 务務 动動 励勵 劲勁 劳勞 势勢 勋勳 勐猛 勚勩 匀勻 匦匭 匮匱 区區 医醫 华華 协協 单單 卖賣 卢盧 卤鹵 卫衛 却卻 厂廠 厅廳 历歷 厉厲 压壓 厌厭 厍厙 厕廁 厢廂 厣厴 厦廈 厨廚 厩廄 厮廝 县縣 叁參 参參 双雙 发發 变變 叙敘 叠疊 叶葉 号號 叹嘆 叽嘰 吁籲 后後 吓嚇 吕呂 吗嗎 听聽 吴吳 启啟 呒嘸 呓囈 呕嘔 呖嚦 呗唄 员員 呙咼 呛嗆 呜嗚 咏詠 咙嚨 咛嚀 咝噝 咤吒 咨諮 咸鹹 响響 哑啞 哒噠 哓嘵 哔嗶 哕噦 哗嘩 哙噲 哜嚌 哝噥 哟喲 唛嘜 唝嗊 唠嘮 唡啢 唢嗩 唤喚 啧嘖 啬嗇 啭囀 啮嚙 啴嘽 啸嘯 喷噴 喽嘍 喾嚳 嗫囁 嗳噯 嘘噓 嘤嚶 嘱囑 噜嚕 噼劈 嚣囂 团團 园園 困睏 囱囪 围圍 囵圇 国國 图圖 圆圓 圹壙 场場 坏壞 块塊 坚堅 坛壇 坜壢 坝壩 坞塢 坟墳 坠墜 垄壟 垅壟 垆壚 垒壘 垦墾 垩堊 垫墊 垭埡 垱壋 垲塏 垴堖 埘塒 埙塤 埚堝 埯垵 堑塹 堕墮 墙牆 壮壯 声聲 壳殼 壶壺 处處 备備 复復 够夠 头頭 夹夾 夺奪 奁奩 奂奐 奋奮 奖獎 奥奧 妆妝 妇婦 妈媽 妩嫵 妪嫗 妫媯 姗姍 姹奼 娄婁 娅婭 娆嬈 娇嬌 娈孌 娲媧 娴嫻 婳嫿 婴嬰 婵嬋 婶嬸 媪媼 嫒嬡 嫔嬪 嫱嬙 孙孫 学學 孪孿 宁寧 宝寶 实實 宠寵 审審 宪憲 宫宮 宽寬 宾賓 寝寢 对對 寻尋 导導 寿壽 将將 尔爾 尘塵 尝嘗 尧堯 尴尷 尸屍 层層 屉屜 届屆 属屬 屡屢 屦屨 屿嶼 岁歲 岂豈 岖嶇 岗崗 岘峴 岚嵐 岛島 岭嶺 岳嶽 峡峽 峣嶢 峤嶠 峥崢 峦巒 崇崇 崂嶗 崃崍 崭嶄 嵘嶸 嵚嶔 嵛崳 嵝嶁 巅巔 巩鞏 巯巰 币幣 帅帥 师師 帐帳 帘簾 帜幟 带帶 帧幀 帮幫 帱幬 幂冪 干幹 并並 广廣 庄莊 庆慶 庐廬 庑廡 库庫 应應 庙廟 庞龐 废廢 廪廩 开開 异異 弃棄 张張 弥彌 弯彎 弹彈 强強 归歸 当當 录錄 彦彥 彻徹 径徑 徕徠 御禦 忆憶 忏懺 忧憂 忾愾 怀懷 态態 怂慫 怃憮 怄慪 怅悵 怆愴 怜憐 总總 怼懟 怿懌 恋戀 恒恆 恳懇 恶惡 恸慟 恹懨 恺愷 恻惻 恼惱 恽惲 悦悅 悫愨 悬懸 悭慳 悮悞 惊驚 惧懼 惨慘 惩懲 惫憊 惬愜 惭慚 惮憚 惯慣 愠慍 愤憤 愦憒 愿願 慑懾 懑懣 懒懶 懔懍 戆戇 戋戔 戏戲 戗戧 战戰 戬戩 戮戮 户戶 扑撲 执執 扩擴 扪捫 扫掃 扬揚 扰擾 抚撫 抛拋 抟摶 抠摳 抡掄 抢搶 护護 报報 抬擡 抵抵 担擔 拟擬 拢攏 拣揀 拥擁 拦攔 拧擰 拨撥 择擇 挂掛 挚摯 挛攣 挜掗 挝撾 挞撻 挟挾 挠撓 挡擋 挢撟 挣掙 挤擠 挥揮 挦撏 捞撈 损損 捡撿 换換 捣搗 据據 掳擄 掴摑 掷擲 掸撣 掺摻 掼摜 揽攬 揿撳 搀攙 搁擱 搂摟 搅攪 携攜 摄攝 摅攄 摆擺 摇搖 摈擯 摊攤 撄攖 撑撐 撵攆 撷擷撸擼 撺攛 擞擻 攒攢 敌敵 敛斂 数數 斋齋 斓斕 斗鬥 斩斬 断斷 无無 旧舊 时時 旷曠 昙曇 昼晝 显顯 晋晉 晓曉 晔曄 晕暈 暂暫 暧曖 曲麯 术術 朴樸 机機 杀殺 杂雜 权權 条條 来來 杨楊 杩榪 杰傑 极極 构構 枞樅 枢樞 枣棗 枥櫪 枧梘 枨棖 枪槍 枫楓 枭梟 柜櫃 柠檸 柽檉 栀梔 栅柵 标標 栈棧 栉櫛 栋棟 栌櫨 栎櫟 栏欄 树樹 栖棲 样樣 栾欒 桠椏 桡橈 桢楨 档檔 桥橋 桦樺 桧檜 桨槳 桩樁 梦夢 梼檮 梾棶 梿槤 检檢 棂欞 椁槨 椟櫝 椠槧 椭橢 楼樓 榄欖 榇櫬 榈櫚 榉櫸 槚檟 槛檻 槟檳 槠櫧 横橫 樯檣 樱櫻 橥櫫 橱櫥 橼櫞 檩檁 欢歡 欤歟 欧歐 歼殲 殁歿 殇殤 残殘 殒殞 殓殮 殚殫 殡殯 殴毆 毁毀 毂轂 毕畢 毙斃 毡氈 气氣 氢氫 氩氬 氲氳 汇匯 汉漢 污污 汤湯 汹洶 沟溝 没沒 沣灃 沤漚 沥瀝 沦淪 沧滄 沨渢 沪滬 泞濘 泪淚 泶澩 泷瀧 泸瀘 泺濼 泻瀉 泼潑 泽澤 泾涇 洁潔 洒灑 洼窪 浃浹 浅淺 浆漿 浇澆 浈湞 浊濁 测測 浍澮 济濟 浏瀏 浐滻 浑渾 浒滸 浓濃 浔潯 涛濤 涝澇 涞淶 涟漣 涠潿 涡渦 涢溳 涣渙 涤滌 润潤 涧澗 涨漲 涩澀 淀澱 渊淵 渌淥 渍漬 渎瀆 渐漸 渔漁 渖瀋 渗滲 温溫 湾灣 湿濕 溃潰 溅濺 溆漵 溇漊 滚滾 滞滯 滟灧 滠灄 满滿 滢瀅 滤濾 滥濫 滦灤 滨濱 滩灘 滪澦 漓灕 漤灠 潆瀠 潇瀟 潋瀲 潍濰 潜潛 潴瀦 澜瀾 濑瀨 濒瀕 灏灝 灭滅 灯燈 灵靈 灾災 灿燦 炀煬 炉爐 炖燉 炜煒 炝熗 点點 炼煉 炽熾 烁爍 烂爛 烃烴 烛燭 烟煙 烦煩 烧燒 烨燁 烩燴 烫燙 烬燼 热熱 焕煥 焖燜 焘燾 煅煆 煳糊 爱愛 爷爺 牍牘 牦犛 牵牽 牺犧 犊犢 状狀 犷獷 犸獁 犹猶 狈狽 狝獮 狞獰 独獨 狭狹 狮獅 狯獪 狰猙 狱獄 狲猻 猃獫 猎獵 猕獼 猡玀 猪豬 猫貓 猬蝟 献獻 獭獺 玑璣 玚瑒 玛瑪 玮瑋 环環 现現 玱瑲 玺璽 珐琺 珑瓏 珰璫 珲琿 琏璉 琐瑣 琼瓊 瑶瑤 瑷璦 璎瓔 瓒瓚 瓮甕 电電 画畫 畅暢 畴疇 疖癤 疗療 疟瘧 疠癘 疡瘍 疬癧 疮瘡 疯瘋 疱皰 疴痾 痈癰 痉痙 痒癢 痖瘂 痨癆 痪瘓 痫癇 瘅癉 瘗瘞 瘘瘻 瘪癟 瘫癱 瘾癮 瘿癭 癞癩 癣癬 皱皺 皲皸 盏盞 盐鹽 监監 盖蓋 盗盜 盘盤 着著 睁睜 睐睞 睑瞼 瞒瞞 瞩矚 矫矯 矶磯 矾礬 矿礦 砀碭 码碼 砖磚 砗硨 砚硯 砜碸 砺礪 砻礱 砾礫 础礎 硁硜 硕碩 硖硤 硗磽 硙磑 硚礄 确確 碍礙 碛磧 碜磣 礼禮 祃禡 祎禕 祢禰 祯禎 祷禱 祸禍 禀稟 禄祿 禅禪 离離 秃禿 秆稈 种種 积積 称稱 秽穢 税稅 稣穌 稳穩 穑穡 穷窮 窃竊 窍竅 窑窯 窜竄 窝窩 窥窺 窦竇 竖豎 竞競 笃篤 笋筍 笔筆 笕筧 笺箋 笼籠 笾籩 筑築 筚篳 筛篩 筜簹 筝箏 筹籌 签簽 简簡 箓籙 箦簀 箧篋 箨籜 箩籮 箪簞 箫簫 篑簣 篓簍 篮籃 篱籬 簖籪 籁籟 籴糴 类類 粜糶 粝糲 粤粵 粪糞 粮糧 糁糝 糇餱 糍餈 糟糟 糨糨 系係 紧緊 累纍 絷縶 纟糹 纠糾 纡紆 红紅 纣紂 纤纖 约約 级級 纨紈 纩纊 纪紀 纫紉 纬緯 纭紜 纯純 纰紕 纱紗 纲綱 纳納 纵縱 纶綸 纷紛 纸紙 纹紋 纺紡 纽紐 纾紓 线線 绀紺 绁絏 绂紱 练練 组組 绅紳 细細 织織 终終 绉縐 绊絆 绋紼 绌絀 绍紹 绎繹 经經 绐紿 绑綁 绒絨 结結 绔絝 绕繞 绖絰 绗絎 绘繪 给給 绚絢 绛絳 络絡 绝絕 绞絞 统統 绠綆 绡綃 绢絹 绣繡 绥綏 绦絛 继繼 绨綈 绩績 绪緒 绫綾 续續 绮綺 绯緋 绰綽 绱緔 绲緄 绳繩 维維 绵綿 绶綬 绷繃 绸綢 绺綹 绻綣 综綜 绽綻 绾綰 绿綠 缀綴 缁緇 缂緙 缃緗 缄緘 缅緬 缆纜 缇緹 缈緲 缉緝 缋繢 缌緦 缍綞 缎緞 缏緶 缑緱 缒縋 缓緩 缔締 缕縷 编編 缗緡 缘緣 缙縉 缚縛 缛縟 缜縝 缝縫 缟縞 缠纏 缡縭 缢縊 缣縑 缤繽 缥縹 缦縵 缧縲 缨纓 缩縮 缪繆 缫繅 缬纈 缭繚 缮繕 缯繒 缰韁 缱繾 缲繰 缳繯 缴繳 缵纘 罂罌 网網 罗羅 罚罰 罢罷 罴羆 羁羈 羟羥 羡羨 翘翹 耢耮 耧耬 耸聳 耻恥 聂聶 聋聾 职職 聍聹 联聯 聩聵 聪聰 肃肅 肠腸 肤膚 肾腎 肿腫 胀脹 胁脅 胆膽 胜勝 胧朧 胨腖 胪臚 胫脛 胶膠 脉脈 脍膾 脏臟 脐臍 脑腦 脓膿 脔臠 脚腳 脱脫 脶腡 脸臉 腊臘 腌醃 腘膕 腻膩 腼靦 腽膃 腾騰 膑臏 臜臢 舆輿 舣艤 舰艦 舱艙 舻艫 艰艱 艳豔 艺藝 节節 芈羋 芗薌 芜蕪 芦蘆 苁蓯 苇葦 苈藶 苋莧 苌萇 苍蒼 苎苧 苏蘇 苘檾 苹蘋 范範 茎莖 茏蘢 茑蔦 茔塋 茕煢 茧繭 荆荊 荐薦 荙薘 荚莢 荛蕘 荜蓽 荞蕎 荟薈 荠薺 荡蕩 荣榮 荤葷 荥滎 荦犖 荧熒 荨蕁 荩藎 荪蓀 荫蔭 荭葒 荮葤 莅蒞 莈沒 莲蓮 莳蒔 莴萵 莶薟 获獲 莸蕕 莺鶯 莼蓴 萚蘀 萝蘿 萤螢 营營 萦縈 萧蕭 萨薩 葱蔥 蒇蕆 蒉蕢 蒋蔣 蒌蔞 蓝藍 蓟薊 蓠蘺 蓣蕷 蓥鎣 蓦驀 蔑衊 蔷薔 蔹蘞 蔺藺 蕲蘄 蕴蘊 薮藪 藓蘚 虏虜 虑慮 虚虛 虫蟲 虬虯 虮蟣 虽雖 虾蝦 虿蠆 蚀蝕 蚁蟻 蚂螞 蚕蠶 蛊蠱 蛎蠣 蛏蟶 蛮蠻 蛰蟄 蛱蛺 蛲蟯 蛳螄 蛴蠐 蜕蛻 蜗蝸 蜡蠟 蝇蠅 蝈蟈 蝉蟬 蝼螻 蝾蠑 螀螿 螨蟎 蟏蠨 衅釁 衔銜 补補 表錶 袅裊 袜襪 袭襲 袯襏 装裝 裆襠 裢褳 裣襝 裤褲 褛褸 褴襤 见見 观觀 规規 觅覓 视視 觇覘 览覽 觉覺 觊覬 觋覡 觌覿 觎覦 觏覯 觐覲 觑覷 觞觴 触觸 詟讋 誉譽 誊謄 讠訁 计計 订訂 讣訃 认認 讥譏 讦訐 讧訌 讨討 让讓 讪訕 讫訖 训訓 议議 讯訊 记記 讱訒 讲講 讳諱 讴謳 讵詎 讶訝 讷訥 许許 讹訛 论論 讼訟 讽諷 设設 访訪 诀訣 证證 诂詁 诃訶 评評 诅詛 识識 诈詐 诉訴 诊診 诋詆 诌謅 词詞 诎詘 诏詔 译譯 诒詒 诓誆 诔誄 试試 诗詩 诘詰 诙詼 诚誠 诛誅 诜詵 话話 诞誕 诟詬 诠詮 诡詭 询詢 诣詣 诤諍 该該 详詳 诧詫 诨諢 诩詡 诫誡 诬誣 语語 诮誚 误誤 诰誥 诱誘 诲誨 诳誑 说說 诵誦 诶誒 请請 诸諸 诹諏 诺諾 读讀 诼諑 诽誹 课課 诿諉 谀諛 谁誰 谂諗 调調 谄諂 谅諒 谆諄 谈談 谊誼 谋謀 谌諶 谍諜 谎謊 谏諫 谐諧 谑謔 谒謁 谓謂 谔諤 谕諭 谖諼 谗讒 谘諮 谙諳 谚諺 谛諦 谜謎 谝諞 谟謨 谠讜 谡謖 谢謝 谣謠 谤謗 谥諡 谦謙 谧謐 谨謹 谩謾 谪謫 谫譾 谬謬 谭譚 谮譖 谯譙 谰讕 谱譜 谲譎 谳讞 谴譴 谵譫 谶讖 谷穀 豁豁 豆豆 象象 豫豫 贞貞 负負 贡貢 财財 责責 贤賢 败敗 账帳 货貨 质質 贩販 贪貪 贫貧 贬貶 购購 贮貯 贯貫 贰貳 贱賤 贲賁 贳貰 贴貼 贵貴 贶貺 贷貸 贸貿 费費 贺賀 贻貽 贼賊 贽贄 贾賈 贿賄 赀貲 赁賃 赂賂 赃贓 资資 赅賅 赆贐 赇賕 赈賑 赉賚 赊賒 赋賦 赌賭 赍齎 赎贖 赏賞 赐賜 赔賠 赓賡 贤賢 赖賴 赘贅 赚賺 赛賽 赞讚 赠贈 赡贍 赢贏 赣贛 赵趙 赶趕 趋趨 趱趲 跃躍 跄蹌 跖蹠 跞躒 践踐 踊踴 踌躊 踪蹤 踬躓 踯躑 蹑躡 蹒蹣 蹰躕 蹿躥 躏躪 车車 轧軋 轨軌 轩軒 轪軑 轫軔 转轉 轭軛 轮輪 软軟 轰轟 轲軻 轳轤 轴軸 轵軹 轶軼 轷軤 轸軫 轹轢 轺軺 轻輕 轼軾 载載 轾輊 轿轎 较較 辄輒 辅輔 辆輛 辇輦 辈輩 辉輝 辊輥 辋輞 辍輟 辎輜 辏輳 辐輻 辑輯 输輸 辔轡 辕轅 辖轄 辗輾 辘轆 辙轍 辚轔 辞辭 辩辯 辫辮 边邊 辽遼 达達 迁遷 过過 迈邁 运運 还還 这這 进進 远遠 违違 连連 迟遲 迩邇 迳逕 迹跡 适適 选選 逊遜 递遞 逦邐 逻邏 遗遺 遥遙 邓鄧 邝鄺 邬鄔 邮郵 邹鄒 邺鄴 邻鄰 郁鬱 郏郟 郐鄶 郑鄭 郓鄆 郦酈 郧鄖 郸鄲 酝醞 酦醱 酱醬 酽釅 酾釃 酿釀 释釋 里裡 鉴鑒 钅釒 钆釓 钇釔 针針 钉釘 钊釗 钋釙 钌釕 钍釷 钎釺 钏釧 钐釤 钒釩 钓釣 钔鍆 钕釹 钗釵 钙鈣 钚鈈 钛鈦 钝鈍 钞鈔 钟鐘 钠鈉 钡鋇 钢鋼 钣鈑 钤鈐 钥鑰 钦欽 钧鈞 钨鎢 钩鉤 钪鈧 钫鈁 钬鈥 钭鈄 钮鈕 钯鈀 钰鈺 钱錢 钲鉦 钳鉗 钴鈷 钵缽 钶鈳 钷鉕 钸鈽 钹鈸 钺鉞 钻鑽 钼鉬 钽鉭 钾鉀 钿鈿 铀鈾 铁鐵 铂鉑 铃鈴 铄鑠 铅鉛 铆鉚 铈鈰 铉鉉 铊鉈 铋鉍 铌鈮 铍鈹 铎鐸 铐銬 铑銠 铒鉺 铕銪 铖鋮 铗鋏 铘鋣 铙鐃 铛鐺 铜銅 铝鋁 铞銱 铟銦 铠鎧 铡鍘 铢銖 铣銑 铤鋌 铥銩 铧鏵 铨銓 铩鎩 铪鉿 铫銚 铬鉻 铭銘 铮錚 铯銫 铰鉸 铱銥 铲鏟 铳銃 铵銨 银銀 铷銣 铸鑄 铹鐒 铺鋪 铻鋙 铼錸 铽鋱 铿鏗 销銷 锁鎖 锂鋰 锃鋥 锄鋤 锅鍋 锆鋯 锇鋨 锈鏽 锉銼 锊鋝 锋鋒 锌鋅 锍鋶 锎鐦 锏鐧 锐銳 锑銻 锒鋃 锓鋟 锔鋦 锕錒 锖錆 锗鍺 锘鍩 错錯 锚錨 锛錛 锜錡 锝鍀 锞錁 锟錕 锡錫 锢錮 锣鑼 锤錘 锥錐 锦錦 锨鍁 锩錈 锪鍃 锫錇 锬錟 锭錠 键鍵 锯鋸 锰錳 锱錙 锲鍥 锴鍇 锵鏘 锶鍶 锷鍔 锸鍤 锹鍬 锺鍾 锻鍛 锼鎪 锽鍠 锾鍰 锿鎄 镀鍍 镁鎂 镂鏤 镇鎮 镉鎘 镊鑷 镍鎳 镏鎦 镐鎬 镑鎊 镒鎰 镓鎵 镔鑌 镖鏢 镗鏜 镘鏝 镙鏍 镛鏞 镜鏡 镝鏑 镞鏃 镟鏇 镠鏐 镡鐔 镢鐝 镣鐐 镦鐓 镧鑭 镨鐠 镩鑹 镪鏹 镫鐙 镬鑊 镭鐳 镯鐲 镰鐮 镱鐿 镲鑔 镳鑣 镴鑞 长長 门門 闩閂 闪閃 闫閆 闭閉 问問 闯闖 闰閏 闱闈 闲閒 闳閎 间間 闵閔 闶閌 闷悶 闸閘 闹鬧 闺閨 闻聞 闼闥 闽閩 闾閭 阀閥 阁閣 阂閡 阃閫 阄鬮 阅閱 阆閬 阈閾 阉閹 阊閶 阋鬩 阌閿 阍閽 阎閻 阏閼 阐闡 阑闌 阒闃 阔闊 阕闋 阖闔 阗闐 阙闕 阚闞 队隊 阳陽 阴陰 阵陣 阶階 际際 陆陸 陇隴 陈陳 陉陘 陕陝 陧隉 陨隕 险險 随隨 隐隱 隶隸 难難 雏雛 雠讎 雳靂 雾霧 霁霽 霉黴 靓靚 静靜 面麵 鞑韃 鞒鞽 鞯韉 韦韋 韧韌 韩韓 韪韙 韫韞 韬韜 颂頌 预預 颅顱 领領 颇頗 颈頸 颉頡 颊頰 颌頜 颍潁 颏頦 颐頤 频頻 颓頹 颖穎 颗顆 题題 额額 颚顎 颛顓 颜顏 颡顙 颢顥 颤顫 颦顰 颧顴 风風 飏颺 飐颭 飑颮 飒颯 飓颶 飔颸 飕颼 飘飄 飙飆 飞飛 饣飠 饥飢 饦飥 饨飩 饩餼 饪飪 饫飫 饬飭 饭飯 饮飲 饯餞 饰飾 饱飽 饲飼 饳飿 饴飴 饵餌 饶饒 饷餉 饸餄 饺餃 饼餅 饽餑 饿餓 馀餘 馁餒 馄餛 馅餡 馆館 馇餷 馈饋 馊餿 馋饞 馍饃 馏餾 馐饈 馑饉 馒饅 馓饊 馔饌 馕饢 马馬 驭馭 驮馱 驯馴 驰馳 驱驅 驳駁 驴驢 驵駔 驶駛 驷駟 驸駙 驹駒 驺騶 驻駐 驼駝 驽駑 驾駕 驿驛 骀駘 骁驍 骂罵 骄驕 骅驊 骆駱 骇駭 骈駢 骊驪 骋騁 验驗 骏駿 骐騏 骑騎 骒騍 骓騅 骖驂 骗騙 骘騭 骚騷 骛騖 骜驁 骝騮 骞騫 骟騸 骠驃 骡騾 骢驄 骣驏 骤驟 骥驥 骧驤 骨骨 髅髏 髋髖 髌髕 鬓鬢 魇魘 鱼魚 鱿魷 鲁魯 鲂魴 鲅鮁 鲆鮃 鲇鮎 鲈鱸 鲋鮒 鲍鮑 鲎鱟 鲐鮐 鲑鮭 鲒鮚 鲔鮪 鲕鮞 鲚鱭 鲛鮫 鲜鮮 鲞鯗 鲟鱘 鲠鯁 鲡鱺 鲢鰱 鲣鰹 鲤鯉 鲥鰣 鲦鰷 鲧鯀 鲨鯊 鲩鯇 鲫鯽 鲭鯖 鲮鯪 鲰鯫 鲱鯡 鲲鯤 鲳鯧 鲴鯝 鲵鯢 鲶鯰 鲷鯛 鲸鯨 鲻鯔 鲼鱝 鲽鰈 鲾鰏 鳃鰓 鳄鱷 鳅鰍 鳆鰒 鳇鰉 鳊鯿 鳋鰠 鳌鰲 鳍鰭 鳎鰨 鳏鰥 鳐鰩 鳓鰳 鳔鰾 鳕鱈 鳖鱉 鳗鰻 鳘鰵 鳙鱅 鳜鱖 鳝鱔 鳞鱗 鸟鳥 鸠鳩 鸡雞 鸢鳶 鸣鳴 鸥鷗 鸦鴉 鸨鴇 鸩鴆 鸪鴣 鸫鶇 鸬鸕 鸭鴨 鸯鴦 鸱鴟 鸲鴝 鸳鴛 鸵鴕 鸶鷥 鸷鷙 鸸鴯 鸹鴰 鸺鵂 鸽鴿 鸾鸞 鸿鴻 鹁鵓 鹂鸝 鹃鵑 鹄鵠 鹅鵝 鹆鵒 鹇鷳 鹈鵜 鹉鵡 鹊鵲 鹋鶓 鹌鵪 鹎鵯 鹏鵬 鹑鶉 鹕鶘 鹗鶚 鹘鶻 鹚鶿 鹛鶥 鹜鶩 鹞鷂 鹣鶼 鹤鶴 鹦鸚 鹧鷓 鹨鷚 鹩鷯 鹪鷦 鹫鷲 鹬鷸 鹭鷺 鹰鷹 鹱鸌 鹳鸛 鹾鹺 麦麥 黄黃 黉黌 黡黶 黩黷 黪黲 黾黽 鼋黿 鼍鼉 鼹鼴 齐齊 齑齏 齿齒 龀齔 龃齟 龄齡 龅齙 龆齠 龇齜 龈齦 龉齬 龊齪 龋齲 龌齷 龙龍 龚龔 龛龕 龟龜
`;

const charMap = new Map();
for (const pair of charPairs.trim().split(/\s+/)) {
  if (pair.length >= 2) {
    charMap.set(pair[0], pair[1]);
  }
}
for (const ambiguousChar of ['干', '系', '表', '面', '累']) {
  charMap.delete(ambiguousChar);
}
charMap.set('页', '頁');
charMap.set('项', '項');
charMap.set('顶', '頂');
charMap.set('须', '須');
charMap.set('顺', '順');
charMap.set('尽', '盡');

const sortedPhrasePairs = phrasePairs
  .slice()
  .sort((a, b) => b[0].length - a[0].length);

const postPhrasePairs = [
  ['設置', '設定'],
  ['默認', '預設'],
  ['軟件', '軟體'],
  ['插件', '外掛'],
  ['網絡', '網路'],
  ['服務器', '伺服器'],
  ['鏈接', '連結'],
  ['文件', '檔案'],
  ['視頻', '影片'],
  ['屏幕', '螢幕'],
  ['郵箱', '信箱'],
  ['登錄', '登入'],
  ['註銷', '登出'],
  ['麵包屑', '麵包屑'],
  ['為准', '為準'],
  ['後台', '後台'],
  ['前台', '前台'],
  ['網站點', '網站'],
  ['站點', '網站'],
];

function replaceAllLiteral(value, search, replacement) {
  return value.split(search).join(replacement);
}

function convertText(value) {
  if (!/[\u3400-\u9fff]/u.test(value)) {
    return value;
  }

  let output = value;
  for (const [source, target] of sortedPhrasePairs) {
    output = replaceAllLiteral(output, source, target);
  }

  output = Array.from(output, (char) => charMap.get(char) || char).join('');

  for (const [source, target] of postPhrasePairs) {
    output = replaceAllLiteral(output, source, target);
  }

  return output;
}

function convertPoQuotedLine(line, inMsgstr) {
  if (!inMsgstr) {
    return line;
  }

  const match = line.match(/^(\s*(?:msgstr(?:\[\d+\])?\s*)?)("(?:(?:\\.)|[^"\\])*")(.*)$/);
  if (!match) {
    return line;
  }

  const rawValue = JSON.parse(match[2]);
  return `${match[1]}${JSON.stringify(convertText(rawValue))}${match[3]}`;
}

function buildTraditionalPo() {
  if (!fs.existsSync(sourcePo)) {
    throw new Error(`Missing source PO: ${sourcePo}`);
  }

  let currentField = '';
  const convertedLines = fs.readFileSync(sourcePo, 'utf8')
    .split(/\r\n|\r|\n/)
    .map((line) => {
      if (line.startsWith('msgid_plural')) {
        currentField = 'msgid_plural';
        return line;
      }
      if (line.startsWith('msgid')) {
        currentField = 'msgid';
        return line;
      }
      if (line.startsWith('msgstr')) {
        currentField = 'msgstr';
        return convertPoQuotedLine(line, true);
      }
      if (line.startsWith('"')) {
        return convertPoQuotedLine(line, currentField === 'msgstr');
      }
      if (line.trim() === '') {
        currentField = '';
      }
      return line;
    });

  let content = convertedLines.join('\n');
  content = content
    .replace('"Language-Team: Chinese (China)\\n"', '"Language-Team: Chinese (Taiwan)\\n"')
    .replace('"Language: zh_CN\\n"', '"Language: zh_TW\\n"');

  if (!content.endsWith('\n')) {
    content += '\n';
  }

  fs.writeFileSync(targetPo, content, 'utf8');
  return targetPo;
}

function parsePoString(line) {
  const quoted = line.match(/"((?:\\.|[^"])*)"/);
  if (!quoted) {
    return '';
  }
  return JSON.parse(`"${quoted[1]}"`);
}

function parsePo(content) {
  const entries = [];
  let current = null;
  let field = null;

  const push = () => {
    if (current) {
      entries.push(current);
    }
    current = null;
    field = null;
  };

  for (const line of content.split(/\r\n|\r|\n/)) {
    if (line.trim() === '') {
      push();
      continue;
    }

    if (!current) {
      current = {
        comments: [],
        msgid: '',
        msgidPlural: null,
        msgstr: '',
        msgstrPlural: new Map(),
      };
    }

    if (line.startsWith('#')) {
      current.comments.push(line);
      continue;
    }

    if (line.startsWith('msgid_plural')) {
      current.msgidPlural = parsePoString(line);
      field = 'msgidPlural';
      continue;
    }

    if (line.startsWith('msgid')) {
      current.msgid = parsePoString(line);
      field = 'msgid';
      continue;
    }

    const pluralMatch = line.match(/^msgstr\[(\d+)\]/);
    if (pluralMatch) {
      const index = Number(pluralMatch[1]);
      current.msgstrPlural.set(index, parsePoString(line));
      field = `msgstr:${index}`;
      continue;
    }

    if (line.startsWith('msgstr')) {
      current.msgstr = parsePoString(line);
      field = 'msgstr';
      continue;
    }

    if (line.startsWith('"') && field) {
      const value = parsePoString(line);
      if (field === 'msgid') {
        current.msgid += value;
      } else if (field === 'msgidPlural') {
        current.msgidPlural += value;
      } else if (field === 'msgstr') {
        current.msgstr += value;
      } else if (field.startsWith('msgstr:')) {
        const index = Number(field.slice('msgstr:'.length));
        current.msgstrPlural.set(index, (current.msgstrPlural.get(index) || '') + value);
      }
    }
  }

  push();
  return entries;
}

function buildMo(entries) {
  const catalog = new Map();

  for (const entry of entries) {
    if (entry.msgidPlural !== null && entry.msgidPlural !== undefined) {
      const original = `${entry.msgid}\u0000${entry.msgidPlural}`;
      const indexes = [...entry.msgstrPlural.keys()].sort((a, b) => a - b);
      const translation = indexes.map((index) => entry.msgstrPlural.get(index) || '').join('\u0000');
      catalog.set(original, translation);
      continue;
    }

    catalog.set(entry.msgid, entry.msgstr || '');
  }

  const originals = [...catalog.keys()].sort();
  const translations = originals.map((original) => catalog.get(original));
  const count = originals.length;
  const tableOffset = 28;
  const originalTableOffset = tableOffset;
  const translationTableOffset = originalTableOffset + count * 8;
  let stringOffset = translationTableOffset + count * 8;

  const originalBuffers = originals.map((value) => Buffer.from(value, 'utf8'));
  const translationBuffers = translations.map((value) => Buffer.from(value, 'utf8'));
  const originalTable = [];
  const translationTable = [];

  for (const buffer of originalBuffers) {
    originalTable.push([buffer.length, stringOffset]);
    stringOffset += buffer.length + 1;
  }

  for (const buffer of translationBuffers) {
    translationTable.push([buffer.length, stringOffset]);
    stringOffset += buffer.length + 1;
  }

  const output = Buffer.alloc(stringOffset);
  output.writeUInt32LE(0x950412de, 0);
  output.writeUInt32LE(0, 4);
  output.writeUInt32LE(count, 8);
  output.writeUInt32LE(originalTableOffset, 12);
  output.writeUInt32LE(translationTableOffset, 16);
  output.writeUInt32LE(0, 20);
  output.writeUInt32LE(0, 24);

  originalTable.forEach(([length, offset], index) => {
    output.writeUInt32LE(length, originalTableOffset + index * 8);
    output.writeUInt32LE(offset, originalTableOffset + index * 8 + 4);
    originalBuffers[index].copy(output, offset);
  });

  translationTable.forEach(([length, offset], index) => {
    output.writeUInt32LE(length, translationTableOffset + index * 8);
    output.writeUInt32LE(offset, translationTableOffset + index * 8 + 4);
    translationBuffers[index].copy(output, offset);
  });

  return output;
}

function compileMo(poPath) {
  const entries = parsePo(fs.readFileSync(poPath, 'utf8'));
  const moPath = poPath.replace(/\.po$/, '.mo');
  fs.writeFileSync(moPath, buildMo(entries));
  return { entries: entries.length, moPath };
}

const poPath = buildTraditionalPo();
const result = compileMo(poPath);

console.log(JSON.stringify({
  po: path.relative(themeRoot, poPath).replace(/\\/g, '/'),
  mo: path.relative(themeRoot, result.moPath).replace(/\\/g, '/'),
  entries: result.entries,
}, null, 2));
