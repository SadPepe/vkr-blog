<?php

namespace Database\Seeders;

<<<<<<< HEAD
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class ArticleSeeder extends Seeder
{
    /**
     * Сколько статей должно быть в базе после запуска.
     * Меняйте число под свои нужды.
     */
    public const ARTICLES_COUNT = 40;

    /**
     * Откуда берём картинки для превью и вставок в текст.
     * Можно переопределить через SEEDER_IMAGES_DIR в .env.
     */
    public const IMAGES_DIR = '/home/anna/Изображения/Wallpapers/Best';

    /**
     * Сколько фотографий берём из папки.
     * 0 — брать все файлы из папки.
     */
    public const IMAGES_COUNT = 0;

    /**
     * Префикс имён скопированных файлов, чтобы не затирать загруженные вручную.
     */
    private const SEED_IMAGE_PREFIX = 'seed-';

    /**
     * Подписи под иллюстрациями — для демонстрации <figcaption>.
     */
    private const IMAGE_CAPTIONS = [
        'Фотография из подборки',
        'Иллюстрация к материалу',
        'Кадр из архива редакции',
        'Фото: личный архив автора',
    ];

    /** @var array<int, string>|null Кэш предложений русского корпуса. */
    private ?array $sentences = null;

    /** @var array<int, array{card: string, post: string}> Колода фото на этот запуск. */
    private array $imageDeck = [];

    /** @var int Позиция в колоде фото. */
    private int $imageDeckIndex = 0;

    /**
     * Создаёт тестовые статьи на русском языке.
     *
     * Каждая статья — «витрина» возможностей редактора: заголовки h2/h3,
     * маркированный список с вложенным, нумерованный список со своим
     * началом, цитата, блок кода, горизонтальная линия, ссылка, жирный /
     * курсив / подчёркивание / зачёркивание / код в строке, выравнивание
     * по центру и 2–3 фотографии с подписями.
     *
     * Повторный запуск не плодит дубликаты: уже засеянные статьи
     * (у них превью начинается с «seed-») получают новый текст и новые
     * фотографии, а недостающие до ARTICLES_COUNT статьи создаются.
     * Количество можно переопределить через SEEDER_ARTICLES_COUNT в .env.
     *
     * Пример запуска:
     *   php artisan db:seed --class=ArticleSeeder
     */
    public function run(): void
    {
        $faker = fake('ru_RU');

        $user = User::orderBy('id')->first();
        if (!$user) {
            $user = User::factory()->create(['name' => 'Тестовый Автор']);
        }
=======
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Support\ImageStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Демо-статьи для ленты и страницы поста.
 *
 * Тексты — связные заглушки на русском, собранные по разделам: лид, абзацы,
 * подзаголовки, списки, цитата, код (для «Технологий») и картинки с подписями.
 * Фото берутся из database/seeders/images и сжимаются в WebP через ImageStore.
 *
 * Сидер полностью заменяет статьи: старые записи, комментарии, лайки и
 * просмотры удаляются, затем создаются новые. Это удобно для демо-стенда.
 */
class ArticleSeeder extends Seeder
{
    /** Сколько статей создавать за один прогон. */
    public const ARTICLES_COUNT = 40;

    /** Префикс имён скопированных файлов, чтобы не затирать загруженные вручную. */
    private const SEED_IMAGE_PREFIX = 'seed-';

    /**
     * 8 категорий × 5 тем. Заголовки специально разные по интонации,
     * чтобы лента не выглядела однообразной.
     */
    private const TOPICS = [
        'beauty' => [
            'Утренний уход без лишних шагов',
            'Как собрать капсульный гардероб на сезон',
            'Текстуры в макияже: матовые и сияющие',
            'Расчёска, которая не вредит волосам',
            'Минимализм в уходе за кожей',
        ],
        'sport' => [
            'Первая неделя бега: с чего начать',
            'Силовая тренировка дома без инвентаря',
            'Растяжка после работы: короткий комплекс',
            'Как выбрать кроссовки для зала',
            'Питание до и после тренировки',
        ],
        'travel' => [
            'Три дня в городе, который вы не знаете',
            'Как собрать лёгкий рюкзак в поездку',
            'Поезд вместо самолёта: плюсы и минусы',
            'Смотровые площадки и лучший свет для фото',
            'Планирование маршрута без спешки',
        ],
        'food' => [
            'Один базовый соус и пять ужинов',
            'Как печь хлеб дома: первые шаги',
            'Сезонные овощи в простых рецептах',
            'Завтрак, который не отнимает утро',
            'Специи, которые меняют вкус блюда',
        ],
        'science' => [
            'Как работают научные гипотезы',
            'Почему мы видим цвета по-разному',
            'Коротко о том, как устроен сон',
            'Как читать научные новости',
            'Микропластик: что известно сегодня',
        ],
        'tech' => [
            'Как устроен современный веб-редактор',
            'Зачем сайту WebP и адаптивные картинки',
            'Кэширование в Laravel без сюрпризов',
            'Что такое доступность интерфейса',
            'Как ускорить загрузку страницы',
        ],
        'art' => [
            'Композиция: как читается кадр',
            'Цвет в иллюстрации: три правила',
            'Музей без очереди: как смотреть картины',
            'Скетчбук как ежедневная практика',
            'Типографика для начинающих',
        ],
        'games' => [
            'Как игры учат принимать решения',
            'Инди-проекты, которые стоит попробовать',
            'Звук в играх: незаметная работа',
            'Геймдизайн: правила и исключения',
            'Игровые миры и их архитектура',
        ],
    ];

    /** Лиды: {title} подставляется заголовком статьи. */
    private const LEADS = [
        '{title} — тема, которая кажется простой только на первый взгляд. В этом материале собраны базовые принципы, примеры и практические шаги, которые можно применить сразу.',
        'Этот материал посвящён теме «{title}». Мы пройдёмся по ключевым понятиям, разберём типичные ошибки и оставим короткий план действий.',
        '«{title}» — вопрос, с которого удобно начать знакомство с темой. Ниже — структурированный разбор без лишней теории.',
        'В статье разбираем «{title}»: что важно знать, на что смотреть в первую очередь и как не потеряться в деталях.',
        'Короткий гид по теме «{title}»: основные идеи, практические ориентиры и несколько примеров для вдохновения.',
    ];

    /** Подписи к фотографиям. */
    private const CAPTIONS = [
        'Иллюстрация к материалу',
        'Фотография из подборки',
        'Кадр из архива редакции',
        'Фото: личный архив автора',
        'Деталь, которая задаёт настроение',
    ];

    /** Общие выводы — чтобы финал статьи не выглядел обрывом. */
    private const CONCLUSIONS = [
        'В итоге главное — не повторить все пункты сразу, а выбрать один-два, которые подходят именно вам. Небольшой последовательный шаг почти всегда полезнее большого плана.',
        'Этот материал — отправная точка. Попробуйте применить хотя бы одну идею на практике и посмотрите, что изменится.',
        'Не обязательно делать всё идеально с первого раза. Важнее вернуться к теме, проверить результат и скорректировать подход.',
        'Если что-то не подошло, это тоже результат. Оставьте то, что работает, и не усложняйте остальное.',
        'Начните с простого, наблюдайте за результатом и добавляйте детали постепенно. Так прогресс будет устойчивее.',
    ];

    /**
     * Текстовые заготовки по категориям: подзаголовки, абзацы, пункты,
     * шаги и цитаты. Абзацы самостоятельные, поэтому их можно собирать
     * в любом порядке без потери смысла.
     */
    private const CONTENT = [
        'beauty' => [
            'headings' => ['С чего начать', 'Что действительно работает', 'Частые ошибки', 'Практический минимум'],
            'paragraphs' => [
                'Красота начинается с привычек, которые легко повторять каждый день. Не обязательно покупать много средств: важнее понять, какие шаги действительно нужны вашей коже и волосам.',
                'Стиль складывается из деталей: посадки, фактуры и цвета. Вещь, которая хорошо сидит, выглядит дороже, чем сложный образ из неподходящих элементов.',
                'Перед покупкой нового средства полезно проверить состав и понять, для какого типа кожи оно предназначено. Универсальных решений мало, зато предсказуемых — достаточно.',
                'Уход не требует много времени, если выстроить последовательность: очищение, увлажнение и защита. Остальные шаги добавляются по необходимости.',
                'Гардероб проще собирать вокруг базовых вещей нейтральных оттенков. Они сочетаются между собой и оставляют место для акцентов.',
            ],
            'list' => [
                'Начните с простого очищения и увлажнения.',
                'Подбирайте средства под свой тип кожи, а не под рекламу.',
                'Проверяйте посадку и состав ткани перед покупкой.',
                'Оставьте в гардеробе вещи, которые носите чаще всего.',
                'Не бойтесь возвращаться к проверенным решениям.',
            ],
            'steps' => [
                'Определите, какие задачи хотите решить.',
                'Составьте короткий список средств или вещей.',
                'Протестируйте новинку на небольшом участке.',
                'Оцените результат через две недели.',
                'Оставьте только то, что действительно удобно.',
            ],
            'quotes' => [
                'Хороший уход — это не количество шагов, а регулярность.',
                'Стиль начинается там, где заканчивается желание соответствовать чужому вкусу.',
                'Лучше меньше средств, но тех, которыми пользуетесь каждый день.',
            ],
        ],
        'sport' => [
            'headings' => ['С чего начать', 'Как построить тренировку', 'Восстановление', 'Что важно помнить'],
            'paragraphs' => [
                'Регулярность важнее интенсивности: короткая тренировка три раза в неделю приносит больше пользы, чем редкие изматывающие занятия.',
                'Перед нагрузкой стоит разогреться, а после — уделить несколько минут растяжке. Это снижает риск дискомфорта и помогает быстрее восстановиться.',
                'Прогресс появляется, когда нагрузка растёт постепенно. Не нужно каждый раз тренироваться на максимум: организму нужно время на адаптацию.',
                'Сон и питание влияют на результат не меньше самих тренировок. Без восстановления даже хорошая программа работает хуже.',
                'Выбирайте активность, которая приносит удовольствие. Тогда поддерживать привычку будет проще, а пропуски — реже.',
            ],
            'list' => [
                'Начинайте с разминки и заканчивайте растяжкой.',
                'Увеличивайте нагрузку небольшими шагами.',
                'Пейте воду и следите за самочувствием.',
                'Оставляйте дни отдыха между интенсивными занятиями.',
                'Записывайте прогресс, чтобы видеть динамику.',
            ],
            'steps' => [
                'Определите цель и удобные дни для занятий.',
                'Соберите простую программу из базовых упражнений.',
                'Начните с комфортной нагрузки.',
                'Добавляйте по одному новому элементу в неделю.',
                'Пересматривайте план раз в месяц.',
            ],
            'quotes' => [
                'Лучшая тренировка — та, которая состоится.',
                'Тело любит постепенность больше, чем рекорды.',
                'Отдых — часть плана, а не пропуск.',
            ],
        ],
        'travel' => [
            'headings' => ['Как спланировать', 'Что взять с собой', 'Как не устать', 'Практические советы'],
            'paragraphs' => [
                'Путешествие начинается с простого плана: даты, бюджет и два-три главных впечатления. Всё остальное можно решить на месте без спешки.',
                'Лёгкий багаж оставляет свободу. Если вещи можно купить по дороге, лучше не брать их с собой.',
                'Маршрут стоит строить вокруг точек, которые важны именно вам, а не вокруг галочек в списке обязательных мест.',
                'Оставляйте в плане пустые часы: именно в них чаще всего случаются лучшие находки.',
                'Заранее проверьте транспорт, время работы музеев и прогноз погоды. Это экономит силы и нервы.',
            ],
            'list' => [
                'Составьте список из двух-трёх главных мест.',
                'Скачайте карты и билеты заранее.',
                'Возьмите запас воды и удобную обувь.',
                'Оставьте время на отдых и импровизацию.',
                'Сделайте копии документов на телефон.',
            ],
            'steps' => [
                'Выберите даты и бюджет поездки.',
                'Наметьте основной маршрут.',
                'Забронируйте жильё и транспорт.',
                'Соберите лёгкий рюкзак.',
                'Проверьте детали за день до выезда.',
            ],
            'quotes' => [
                'Хорошая поездка — это не количество городов, а качество внимания.',
                'Иногда лучший план — оставить место для случайности.',
                'Чем легче рюкзак, тем больше остаётся сил на впечатления.',
            ],
        ],
        'food' => [
            'headings' => ['С чего начать', 'Базовые принципы', 'Как упростить готовку', 'Практические советы'],
            'paragraphs' => [
                'Домашняя еда не требует сложных техник. Достаточно нескольких базовых навыков и понимания, как сочетаются вкусы.',
                'Свежие сезонные продукты почти не нуждаются в украшении. Соль, масло и немного специй раскрывают их лучше соусов.',
                'Готовить проще, когда под рукой есть базовый набор ингредиентов и один-два проверенных рецепта.',
                'Вкус блюда часто решают мелочи: температура сковороды, время обжарки и правильная последовательность шагов.',
                'Пробуйте по ходу готовки и записывайте удачные пропорции. Так появляются собственные рецепты.',
            ],
            'list' => [
                'Держите дома базовые крупы, масло и специи.',
                'Начинайте с простых рецептов из трёх-пяти шагов.',
                'Используйте сезонные овощи и зелень.',
                'Пробуйте блюдо до подачи и корректируйте вкус.',
                'Записывайте удачные сочетания.',
            ],
            'steps' => [
                'Выберите один простой рецепт.',
                'Подготовьте продукты заранее.',
                'Следите за температурой и временем.',
                'Попробуйте и скорректируйте вкус.',
                'Запишите, что стоит изменить в следующий раз.',
            ],
            'quotes' => [
                'Хороший вкус начинается с простых продуктов.',
                'Готовка — это привычка, а не талант.',
                'Самое сложное в рецепте — начать.',
            ],
        ],
        'science' => [
            'headings' => ['Как это работает', 'Что известно', 'Как проверить', 'Что важно помнить'],
            'paragraphs' => [
                'Наука строится на проверяемых гипотезах. Идея становится полезной, когда её можно проверить и описать так, чтобы другие исследователи могли повторить результат.',
                'Любое исследование отвечает на узкий вопрос. Чтобы сделать общий вывод, нужно учитывать десятки работ и их ограничения.',
                'Научные новости часто упрощают картину. Полезно смотреть на первоисточник и понимать, на каком объёме данных сделаны выводы.',
                'Ошибки и опровержения — нормальная часть науки. Они помогают уточнять модели, а не отменяют весь процесс.',
                'Чем сложнее тема, тем важнее различать установленные факты, рабочие гипотезы и предположения.',
            ],
            'list' => [
                'Проверяйте, кто и где опубликовал исследование.',
                'Смотрите на размер выборки и методы.',
                'Отличайте корреляцию от причинности.',
                'Ищите независимые подтверждения.',
                'Читайте ограничения исследования.',
            ],
            'steps' => [
                'Сформулируйте вопрос, на который ищете ответ.',
                'Найдите обзорные публикации по теме.',
                'Проверьте первоисточники.',
                'Сравните выводы разных работ.',
                'Отметьте, что осталось неизвестным.',
            ],
            'quotes' => [
                'Наука — это способ задавать вопросы точнее.',
                'Один результат — ещё не закономерность.',
                'Хороший вывод всегда знает свои границы.',
            ],
        ],
        'tech' => [
            'headings' => ['Как это работает', 'С чего начать', 'Что влияет на результат', 'Практические советы'],
            'paragraphs' => [
                'Современный веб-редактор сочетает содержание и интерфейс: текст хранится как структура, а отображение зависит от правил стилей.',
                'Скорость страницы складывается из многих деталей: размера картинок, кэша, количества запросов и порядка загрузки ресурсов.',
                'Кэширование экономит время, но требует аккуратности. Важно понимать, что именно кэшируется и когда данные должны обновиться.',
                'Доступность интерфейса — это не дополнительная функция, а часть качества. Сайт должен работать с клавиатуры и читаться скринридерами.',
                'Простое решение обычно надёжнее сложного. Чем меньше скрытой логики, тем проще поддерживать проект.',
            ],
            'list' => [
                'Сжимайте изображения и используйте современные форматы.',
                'Настройте кэш статики и конфигурации.',
                'Проверяйте страницы на медленном соединении.',
                'Следите за контрастом и размерами шрифта.',
                'Измеряйте результат до и после изменений.',
            ],
            'steps' => [
                'Определите узкое место по метрикам.',
                'Внесите одно изменение за раз.',
                'Проверьте результат в браузере.',
                'Сравните показатели.',
                'Закрепите удачное решение в проекте.',
            ],
            'quotes' => [
                'Быстро — значит предсказуемо для пользователя.',
                'Лучшая оптимизация — та, которую можно измерить.',
                'Доступность начинается с внимания к деталям.',
            ],
        ],
        'art' => [
            'headings' => ['С чего начать', 'Как читать работы', 'Что помогает автору', 'Практические советы'],
            'paragraphs' => [
                'Композиция направляет взгляд: зритель сначала видит главное, затем — детали. Простые правила помогают сделать кадр или макет понятнее.',
                'Цвет задаёт настроение, но работает в связке с формой и контрастом. Даже сдержанная палитра может быть выразительной.',
                'Ежедневная практика важнее редких больших проектов. Небольшой скетч каждый день учит видеть и быстрее принимать решения.',
                'В музее полезно сначала посмотреть на работу целиком, а потом подойти ближе. Так видно и замысел, и технику.',
                'Типографика — это ритм и иерархия. Хорошо подобранный шрифт и отступы влияют сильнее декоративных элементов.',
            ],
            'list' => [
                'Начните с простых форм и крупных пятен.',
                'Проверяйте композицию на небольшом эскизе.',
                'Ограничьте палитру двумя-тремя цветами.',
                'Используйте один акцентный элемент.',
                'Смотрите на работу издалека и вблизи.',
            ],
            'steps' => [
                'Выберите тему и соберите референсы.',
                'Сделайте быстрый эскиз.',
                'Определите главный объект и фон.',
                'Добавьте детали только там, где нужно.',
                'Сделайте паузу и оцените результат.',
            ],
            'quotes' => [
                'Простота требует больше смелости, чем сложность.',
                'Хорошая композиция читается без подписи.',
                'Практика важнее вдохновения.',
            ],
        ],
        'games' => [
            'headings' => ['Как это работает', 'Что делает игру интересной', 'С чего начать', 'Практические советы'],
            'paragraphs' => [
                'Игра удерживает внимание, когда правила понятны, а решения имеют последствия. Простая механика часто работает лучше сложной системы.',
                'Звук в играх незаметен, пока он хорош. Он подсказывает состояние мира и помогает ориентироваться без лишних элементов интерфейса.',
                'Инди-проекты часто выигрывают за счёт ясной идеи и аккуратной реализации, а не размера команды.',
                'Уровень сложности — это инструмент повествования. Иногда пауза или небольшое препятствие говорит больше, чем длинный текст.',
                'Хороший игровой мир устроен логично: игрок может предсказать, что произойдёт, и это создаёт доверие.',
            ],
            'list' => [
                'Начинайте с одной понятной механики.',
                'Проверяйте идею на коротких сессиях.',
                'Следите за читаемостью интерфейса.',
                'Используйте звук как подсказку.',
                'Тестируйте игру на новых людях.',
            ],
            'steps' => [
                'Сформулируйте главную идею игры.',
                'Сделайте минимальный прототип.',
                'Проверьте, понятны ли правила.',
                'Добавьте обратную связь для игрока.',
                'Улучшайте то, что сработало.',
            ],
            'quotes' => [
                'Игра начинается с интересного решения.',
                'Правила важнее графики.',
                'Лучший тест — взгляд нового игрока.',
            ],
        ],
    ];

    /**
     * Удаляет старые статьи со связанными данными и файлами,
     * затем создаёт новый набор демо-публикаций.
     */
    public function run(): void
    {
        $this->purgeExistingArticles();

        $user = User::orderBy('id')->first()
            ?: User::factory()->create([
                'name' => 'Тестовый Автор',
                'username' => 'test',
                'email' => 'test@example.com',
            ]);
>>>>>>> my-saved-changes

        $categorySlugs = Category::pluck('slug')->all();
        $images = $this->prepareImages();

        if (!$images) {
            $this->command?->warn('Картинки не найдены, статьи будут без изображений.');
        }

<<<<<<< HEAD
        // Колода фото на этот запуск: идём по ней по кругу, чтобы каждое
        // фото из папки гарантированно попало хотя бы в одну статью.
        $this->imageDeck = $images;
        shuffle($this->imageDeck);
        $this->imageDeckIndex = 0;

        // env() возвращает null при закешированном конфиге — тогда берём значение по умолчанию.
        $configured = env('SEEDER_ARTICLES_COUNT');
        $limit = ($configured === null || $configured === '')
            ? self::ARTICLES_COUNT
            : max(0, (int) $configured);

        // Статьи, созданные этим сидером (у них превью — скопированный файл).
        $seeded = Article::query()
            ->where('card_image_path', 'like', '%' . self::SEED_IMAGE_PREFIX . '%')
            ->orderBy('id')
            ->get();

        // Освежаем текст и фото уже существующих статей.
        foreach ($seeded as $article) {
            $picked = $this->pickImages($faker);

            if ($picked) {
                $article->card_image_path = $picked[0]['card'];
            }

            $article->content = $this->buildContent($faker, $picked);
            $article->save();
        }

        // Досоздаём статьи, если их меньше заданного количества.
        $toCreate = max(0, $limit - $seeded->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $title = $this->title($faker);
            $picked = $this->pickImages($faker);
=======
        $topics = $this->topics();
        $limit = $this->articleLimit();
        $now = now();
        $imageIndex = 0;

        foreach (array_slice($topics, 0, $limit) as $index => [$title, $category]) {
            $picked = [];

            if ($images) {
                $picked[] = $images[$imageIndex % count($images)];
                $picked[] = $images[($imageIndex + 1) % count($images)];
                $imageIndex += 2;
            }

            $content = $this->buildContent($title, $category, $picked, $index);
>>>>>>> my-saved-changes

            $article = Article::create([
                'title' => $title,
                'slug' => $this->uniqueSlug($title),
<<<<<<< HEAD
                'excerpt' => $this->sentence($faker, 120),
                'content' => $this->buildContent($faker, $picked),
                'card_image_path' => $picked[0]['card'] ?? null,
                'user_id' => $user->id,
                'category_slug' => $categorySlugs ? $faker->randomElement($categorySlugs) : null,
                'views_count' => $faker->numberBetween(0, 600),
                'likes_count' => $faker->numberBetween(0, 80),
                'comments_count' => 0,
            ]);

            // Разносим даты публикации по последним трём месяцам,
            // чтобы «Популярное за неделю» и «Похожие» имели данные
            $article->created_at = $faker->dateTimeBetween('-3 months', 'now');
            $article->save();
        }

        $this->command?->info(sprintf(
            'Статьи: обновлено %d, создано %d.',
            $seeded->count(),
            $toCreate
        ));
    }

    /**
     * Копирует фотографии из папки в публичный диск и возвращает пути.
     *
     * По умолчанию берутся все файлы папки; порядок перемешивается,
     * а фото внутри статьи выбираются отдельно для каждой статьи.
     *
     * @return array<int, array{card: string, post: string}>
     */
    private function prepareImages(): array
    {
        $sourceDir = env('SEEDER_IMAGES_DIR', self::IMAGES_DIR);

        if (!is_dir($sourceDir)) {
            $this->command?->warn("Папка с картинками не найдена: {$sourceDir}");
=======
                'excerpt' => $this->makeExcerpt($content),
                'content' => $content,
                // Отдельной обложки нет: превью — первая картинка из текста.
                'card_image_path' => $picked[0] ?? null,
                'user_id' => $user->id,
                'category_slug' => in_array($category, $categorySlugs, true) ? $category : null,
                'views_count' => random_int(20, 900),
                'likes_count' => random_int(0, 90),
                'comments_count' => 0,
            ]);

            $createdAt = $now->copy()
                ->subDays(($limit - 1 - $index) * 2)
                ->setTime(random_int(8, 21), random_int(0, 59));

            $article->created_at = $createdAt;
            $article->updated_at = $createdAt;
            $article->save();
        }

        $this->command?->info(sprintf('Создано статей: %d.', min($limit, count($topics))));
    }

    /**
     * Плоский список тем: [заголовок, категория]. Перемешиваем, чтобы
     * в ленте категории не шли строго блоками по пять штук.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function topics(): array
    {
        $topics = [];

        foreach (self::TOPICS as $category => $titles) {
            foreach ($titles as $title) {
                $topics[] = [$title, $category];
            }
        }

        shuffle($topics);

        return $topics;
    }

    private function articleLimit(): int
    {
        $configured = config('seeder.articles_count');

        if ($configured === null || $configured === '') {
            return self::ARTICLES_COUNT;
        }

        return max(0, (int) $configured);
    }

    /**
     * Собирает HTML статьи из блоков редактора: абзацы, подзаголовки,
     * списки, цитату, код и изображения с подписями.
     *
     * @param array<int, string> $images абсолютные URL картинок из /storage
     */
    private function buildContent(string $title, string $category, array $images, int $index): string
    {
        $content = self::CONTENT[$category];

        $lead = str_replace(
            '{title}',
            $title,
            $this->pick(self::LEADS, $index),
        );

        $paragraph = fn (int $offset): string =>
            '<p>' . $this->esc($this->pick($content['paragraphs'], $index + $offset)) . '</p>';

        $blocks = [
            '<p>' . $this->esc($lead) . '</p>',
            $paragraph(0),
        ];

        if (isset($images[0])) {
            $blocks[] = $this->figure($images[0], $this->pick(self::CAPTIONS, $index));
        }

        $blocks[] = '<h2>' . $this->esc($this->pick($content['headings'], $index)) . '</h2>';
        $blocks[] = $paragraph(1);
        $blocks[] = $this->bulletList($content['list'], $index);
        $blocks[] = '<h3>' . $this->esc($this->pick($content['headings'], $index + 1)) . '</h3>';
        $blocks[] = '<blockquote><p>'
            . $this->esc($this->pick($content['quotes'], $index))
            . '</p></blockquote>';
        $blocks[] = $paragraph(2);
        $blocks[] = $this->inlineFormatting();
        $blocks[] = '<h2>' . $this->esc($this->pick($content['headings'], $index + 2)) . '</h2>';
        $blocks[] = $paragraph(3);
        $blocks[] = $this->orderedList($content['steps'], $index);

        if ($category === 'tech') {
            $blocks[] = $this->codeBlock();
        }

        if (isset($images[1])) {
            $blocks[] = $this->figure($images[1], $this->pick(self::CAPTIONS, $index + 1));
        }

        $blocks[] = '<h2>Что в итоге</h2>';
        $blocks[] = '<p>' . $this->esc($this->pick(self::CONCLUSIONS, $index)) . '</p>';

        return implode("\n\n", $blocks);
    }

    /**
     * @param array<int, string> $items
     */
    private function bulletList(array $items, int $index): string
    {
        $items = array_slice($this->rotate($items, $index), 0, 4);

        $html = array_map(
            fn (string $item): string => '<li><p>' . $this->esc($item) . '</p></li>',
            $items,
        );

        return '<ul>' . implode('', $html) . '</ul>';
    }

    /**
     * @param array<int, string> $steps
     */
    private function orderedList(array $steps, int $index): string
    {
        $html = array_map(
            fn (string $step): string => '<li><p>' . $this->esc($step) . '</p></li>',
            $this->rotate($steps, $index),
        );

        return '<ol>' . implode('', $html) . '</ol>';
    }

    private function figure(string $src, string $caption): string
    {
        return '<figure>'
            . '<img src="' . $this->esc($src) . '" alt="' . $this->esc($caption) . '">'
            . '<figcaption>' . $this->esc($caption) . '</figcaption>'
            . '</figure>';
    }

    /**
     * Абзац со строчным форматированием — показывает возможности редактора.
     */
    private function inlineFormatting(): string
    {
        return '<p>Попробуйте <strong>выделить главное</strong>, отметить '
            . '<em>важные детали</em> и <u>проверить</u> результат. В тексте уместны '
            . '<code>короткие пометки</code>, <s>устаревшие советы</s> и '
            . '<a href="https://laravel.com/docs" target="_blank" rel="noopener noreferrer">ссылки на документацию</a>.</p>';
    }

    private function codeBlock(): string
    {
        $code = <<<'PHP'
$posts = Article::query()
    ->with('user')
    ->latest()
    ->take(5)
    ->get();

foreach ($posts as $post) {
    echo $post->title . PHP_EOL;
}
PHP;

        return '<pre><code class="language-php">'
            . htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</code></pre>';
    }

    /**
     * Удаляет все статьи и связанные с ними записи, а также старые
     * seed-картинки, чтобы не копился мусор.
     */
    private function purgeExistingArticles(): void
    {
        $articles = Article::query()->get();

        foreach ($articles as $article) {
            $this->deleteArticleFiles($article);
        }

        // Внешних ключей в схеме нет, поэтому связанные таблицы чистим вручную.
        DB::table('comments')->delete();
        DB::table('comment_likes')->delete();
        DB::table('likes')->delete();
        DB::table('article_views')->delete();
        Article::query()->delete();

        foreach (['images', 'article-cards'] as $directory) {
            foreach (Storage::disk('public')->files($directory) as $file) {
                if (str_starts_with(basename($file), self::SEED_IMAGE_PREFIX)) {
                    Storage::disk('public')->delete($file);
                }
            }
        }
    }

    private function deleteArticleFiles(Article $article): void
    {
        if ($article->card_image_path) {
            $relative = ltrim(str_replace('/storage/', '', $article->card_image_path), '/');
            Storage::disk('public')->delete($relative);
        }

        if ($article->content && preg_match_all('#/storage/images/([^"\'\s>]+)#i', $article->content, $matches)) {
            foreach (array_unique($matches[1]) as $file) {
                Storage::disk('public')->delete('images/' . $file);
            }
        }
    }

    /**
     * Копирует фото из database/seeders/images (или SEEDER_IMAGES_DIR)
     * в публичный диск, сжимая в WebP без обрезки.
     *
     * @return array<int, string> абсолютные URL вида /storage/images/...
     */
    private function prepareImages(): array
    {
        $sourceDir = $this->imagesDir();

        if (!is_dir($sourceDir)) {
            $this->command?->warn("Папка с картинками не найдена: {$sourceDir}");

>>>>>>> my-saved-changes
            return [];
        }

        $files = [];
<<<<<<< HEAD
=======

>>>>>>> my-saved-changes
        foreach (scandir($sourceDir) ?: [] as $file) {
            $path = $sourceDir . DIRECTORY_SEPARATOR . $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (is_file($path) && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $files[] = $file;
            }
        }

        shuffle($files);

<<<<<<< HEAD
        if (self::IMAGES_COUNT > 0) {
            $files = array_slice($files, 0, self::IMAGES_COUNT);
        }

        $disk = Storage::disk('public');
=======
>>>>>>> my-saved-changes
        $images = [];
        $used = [];

        foreach ($files as $file) {
<<<<<<< HEAD
            $name = self::SEED_IMAGE_PREFIX . $this->safeImageName($file);

            // Одинаковые «безопасные» имена не должны затирать друг друга.
            if (isset($used[$name])) {
                $name = self::SEED_IMAGE_PREFIX . $this->safeImageName($file, md5($file));
            }
            $used[$name] = true;

            $source = $sourceDir . DIRECTORY_SEPARATOR . $file;

            // Одна и та же картинка используется и как превью, и как вставка в тексте.
            foreach (['article-cards', 'images'] as $directory) {
                if (!$disk->exists($directory . '/' . $name)) {
                    $disk->put($directory . '/' . $name, file_get_contents($source));
                }
            }

            $images[] = [
                'card' => '/storage/article-cards/' . $name,
                'post' => '/storage/images/' . $name,
            ];
=======
            $base = self::SEED_IMAGE_PREFIX . $this->safeImageName($file);

            if (isset($used[$base])) {
                $base .= '-' . substr(md5($file), 0, 8);
            }
            $used[$base] = true;

            $source = $sourceDir . DIRECTORY_SEPARATOR . $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            $path = ImageStore::storePath(
                $source,
                'images',
                $base,
                ImageStore::DEFAULT_MAX_WIDTH,
                ImageStore::DEFAULT_MAX_HEIGHT,
                ImageStore::DEFAULT_QUALITY,
                $extension,
            );

            $images[] = '/storage/' . $path;
>>>>>>> my-saved-changes
        }

        return $images;
    }

<<<<<<< HEAD
=======
    private function imagesDir(): string
    {
        // Настройка читается через config(), чтобы работать и при config:cache.
        $configured = config('seeder.images_dir');

        return $configured ?: database_path('seeders/images');
    }

>>>>>>> my-saved-changes
    /**
     * Имя файла для публичного диска: без кавычек, пробелов и прочих
     * символов, которые ломают URL и регулярки очистки картинок.
     */
<<<<<<< HEAD
    private function safeImageName(string $file, string $suffix = ''): string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
=======
    private function safeImageName(string $file): string
    {
>>>>>>> my-saved-changes
        $base = pathinfo($file, PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_-]+/u', '-', $base);
        $base = trim($base, '-_');

<<<<<<< HEAD
        if ($base === '') {
            $base = 'image';
        }

        if ($suffix !== '') {
            $base .= '-' . $suffix;
        }

        return $base . '.' . $extension;
    }

    /**
     * Выбирает фото для одной статьи: первое — превью, остальные (2–3) —
     * иллюстрации внутри текста.
     *
     * Фото берутся из «колоды» по кругу, поэтому все файлы папки
     * используются, а внутри статьи порядок перемешивается.
     *
     * @return array<int, array{card: string, post: string}>
     */
    private function pickImages($faker): array
    {
        if (!$this->imageDeck) {
            return [];
        }

        $count = 1 + $faker->numberBetween(2, 3);
        $picked = [];

        for ($i = 0; $i < $count; $i++) {
            $picked[] = $this->imageDeck[$this->imageDeckIndex % count($this->imageDeck)];
            $this->imageDeckIndex++;
        }

        shuffle($picked);

        return $picked;
=======
        return $base === '' ? 'image' : $base;
>>>>>>> my-saved-changes
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

<<<<<<< HEAD
    /**
     * Собирает HTML статьи со всеми блоками редактора.
     *
     * @param array<int, array{card: string, post: string}> $picked
     */
    private function buildContent($faker, array $picked = []): string
    {
        $blocks = [
            '<p>' . $this->esc($this->sentence($faker, 280)) . '</p>',
            $this->inlineFormatting($faker),

            '<h2>' . $this->esc($this->heading($faker, 30, 70)) . '</h2>',
            '<p>' . $this->esc($this->sentence($faker, 220)) . '</p>',
            $this->bulletList($faker),

            '<h3>' . $this->esc($this->heading($faker, 25, 60)) . '</h3>',
            $this->orderedList($faker),
            $this->blockquote($faker),
            $this->centeredNote($faker),

            '<h2>' . $this->esc($this->heading($faker, 30, 70)) . '</h2>',
            '<p>' . $this->esc($this->sentence($faker, 200)) . '</p>',
            $this->codeBlock(),
            '<p>' . $this->esc($this->sentence($faker, 180)) . ' '
                . 'Полезные материалы ищите в <a href="https://laravel.com/docs/queries" target="_blank" rel="noopener noreferrer">документации Laravel</a>.</p>',
            '<hr>',

            '<h3>' . $this->esc($this->heading($faker, 25, 60)) . '</h3>',
            '<p>' . $this->esc($this->sentence($faker, 240)) . '</p>',
        ];

        // Превью уже стоит в шапке — внутри текста используем остальные фото.
        if ($picked) {
            $blocks = $this->insertImages($blocks, array_slice($picked, 1), $faker);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * Абзац со всем строчным форматированием редактора:
     * жирный, курсив, подчёркивание, зачёркивание, код и ссылка.
     */
    private function inlineFormatting($faker): string
    {
        return '<p>'
            . $this->esc($this->sentence($faker, 70)) . ' '
            . '<strong>' . $this->esc($this->sentence($faker, 55)) . '</strong> '
            . $this->esc($this->sentence($faker, 60)) . ' '
            . '<em>' . $this->esc($this->sentence($faker, 50)) . '</em> '
            . '<u>' . $this->esc($this->sentence($faker, 45)) . '</u> '
            . '<s>' . $this->esc($this->sentence($faker, 45)) . '</s> '
            . 'Команда для запуска: <code>php artisan migrate --seed</code>.'
            . '</p>';
    }

    /**
     * Маркированный список с вложенным подсписком.
     */
    private function bulletList($faker): string
    {
        $items = [];
        $total = $faker->numberBetween(3, 4);

        for ($i = 0; $i < $total; $i++) {
            $items[] = '<li><p>' . $this->esc($this->sentence($faker, $faker->numberBetween(50, 110))) . '</p></li>';
        }

        $nested = [];
        for ($i = 0; $i < 2; $i++) {
            $nested[] = '<li><p>' . $this->esc($this->sentence($faker, 60)) . '</p></li>';
        }

        $index = min(1, count($items) - 1);
        $items[$index] = '<li><p>'
            . $this->esc($this->sentence($faker, 70))
            . '</p><ul>' . implode('', $nested) . '</ul></li>';

        return '<ul>' . implode('', $items) . '</ul>';
    }

    /**
     * Нумерованный список, который начинается не с единицы.
     */
    private function orderedList($faker): string
    {
        $start = $faker->numberBetween(2, 7);
        $items = [];

        for ($i = 0; $i < 4; $i++) {
            $items[] = '<li><p>' . $this->esc($this->sentence($faker, $faker->numberBetween(50, 100))) . '</p></li>';
        }

        return '<ol start="' . $start . '">' . implode('', $items) . '</ol>';
    }

    private function blockquote($faker): string
    {
        return '<blockquote><p>' . $this->esc($this->sentence($faker, 170)) . '</p></blockquote>';
    }

    /**
     * Абзац с выравниванием по центру — редактор пишет это в style.
     */
    private function centeredNote($faker): string
    {
        return '<p style="text-align: center;"><em>' . $this->esc($this->sentence($faker, 120)) . '</em></p>';
    }

    /**
     * Блок кода: ровно то, что сохраняет TipTap (pre > code.language-php).
     */
    private function codeBlock(): string
    {
        $code = <<<'PHP'
$articles = Article::query()
    ->with('user', 'category')
    ->latest()
    ->take(5)
    ->get();

foreach ($articles as $article) {
    echo $article->title . PHP_EOL;
}
PHP;

        return '<pre><code class="language-php">'
            . htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</code></pre>';
    }

    /**
     * Заголовок: целое короткое предложение из корпуса, без обрывов на полуслове.
     */
    private function title($faker): string
    {
        return $this->heading($faker, 35, 90);
    }

    /**
     * Заголовок нужной длины из корпуса или, если подходящего нет, из текста.
     */
    private function heading($faker, int $min, int $max): string
    {
        $candidates = array_values(array_filter(
            $this->russianSentences(),
            fn ($sentence) => mb_strlen($sentence) >= $min
                && mb_strlen($sentence) <= $max
                && !preg_match('/^[«»"—–]/u', $sentence)
        ));

        if ($candidates) {
            return rtrim($faker->randomElement($candidates), '.');
        }

        return rtrim($this->truncateAtWord($this->text($faker, $max), $max), '.');
    }

    /**
     * Готовый к вставке в HTML фрагмент: с заглавной буквы и точкой в конце.
     */
    private function sentence($faker, int $chars): string
    {
        $text = $this->text($faker, $chars);
        $text = preg_replace('/^[\s—–-]+/u', '', $text);

        if ($text === '') {
            return $text;
        }

        $text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);

        // Реплики из корпуса обрываются на полуслове — ставим точку,
        // если знака конца предложения нет.
        if (!preg_match('/[.!?…]$/u', $text)) {
            $text = preg_replace('/[\s,;:—–-]+$/u', '', $text) . '.';
        }

        return $text;
    }

    /**
     * Осмысленный русский текст нужной длины.
     * realText в Faker ru_RU требует расширение iconv, поэтому есть запасной путь.
     */
    private function text($faker, int $chars): string
    {
        if (function_exists('iconv')) {
            return rtrim($faker->realText($chars));
        }

        $sentences = $this->russianSentences();
        $text = '';

        while (mb_strlen($text) < $chars) {
            $text .= ($text === '' ? '' : ' ') . $faker->randomElement($sentences);
        }

        $text = $this->truncateAtWord($text, $chars);

        // Реплики из корпуса часто начинаются с тире — в тексте статьи оно лишнее.
        return preg_replace('/^[\s—–-]+/u', '', $text);
    }

    /**
     * Экранирует текст, чтобы кавычки и амперсанды не ломали HTML.
     */
=======
    private function makeExcerpt(string $html): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

        return Str::limit($text, 200, '');
    }

>>>>>>> my-saved-changes
    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
<<<<<<< HEAD
     * Отрезает текст по границе слова, чтобы не рвать слово пополам.
     */
    private function truncateAtWord(string $text, int $chars): string
    {
        if (mb_strlen($text) <= $chars) {
            return $text;
        }

        $cut = mb_substr($text, 0, $chars);
        $space = mb_strrpos($cut, ' ');
        $cut = $space ? mb_substr($cut, 0, $space) : $cut;

        // rtrim() работает с байтами: многобайтовый «—» в charlist рвёт UTF-8.
        return preg_replace('/[\s,;:—–-]+$/u', '', $cut);
    }

    /**
     * Предложения из русского корпуса Faker (по умолчанию — «Мёртвые души»).
     *
     * @return array<int, string>
     */
    private function russianSentences(): array
    {
        if ($this->sentences !== null) {
            return $this->sentences;
        }

        $baseText = (new \ReflectionClass(\Faker\Provider\ru_RU\Text::class))
            ->getStaticPropertyValue('baseText');

        $normalized = preg_replace('/\s+/u', ' ', $baseText);
        $parts = preg_split('/(?<=[.!?])\s+/u', $normalized) ?: [];

        $this->sentences = array_values(array_filter(
            array_map('trim', $parts),
            fn ($sentence) => mb_strlen($sentence) > 40
        ));

        return $this->sentences;
    }

    /**
     * Вставляет картинки после случайных абзацев, порядок каждый раз новый.
     *
     * @param array<int, string> $blocks
     * @param array<int, array{card: string, post: string}> $images
     * @return array<int, string>
     */
    private function insertImages(array $blocks, array $images, $faker): array
    {
        $paragraphIndexes = array_keys(array_filter(
            $blocks,
            fn ($block) => str_starts_with($block, '<p')
        ));

        if (!$paragraphIndexes || !$images) {
            return $blocks;
        }

        $images = array_values($images);
        shuffle($images);

        $count = min(count($images), count($paragraphIndexes));
        $targets = $faker->randomElements($paragraphIndexes, $count);
        sort($targets);

        // Идём с конца, чтобы вставка не сбивала индексы ещё не обработанных абзацев.
        for ($i = $count - 1; $i >= 0; $i--) {
            $image = $images[$i];
            $figure = '<figure>'
                . '<img src="' . $image['post'] . '" alt="' . $this->esc($this->imageAlt($image['post'])) . '">'
                . '<figcaption>' . $this->esc($faker->randomElement(self::IMAGE_CAPTIONS)) . '</figcaption>'
                . '</figure>';

            array_splice($blocks, $targets[$i] + 1, 0, [$figure]);
        }

        return $blocks;
    }

    /**
     * alt для картинки из имени файла — он хотя бы описывает кадр.
     */
    private function imageAlt(string $path): string
    {
        $name = pathinfo(basename($path), PATHINFO_FILENAME);
        $name = str_replace(['seed-', '_'], ['', ' '], $name);

        return 'Фото: ' . trim($name);
=======
     * @param array<int, mixed> $items
     * @return mixed
     */
    private function pick(array $items, int $index)
    {
        return $items[$index % count($items)];
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, mixed>
     */
    private function rotate(array $items, int $index): array
    {
        $offset = $index % count($items);

        return array_merge(array_slice($items, $offset), array_slice($items, 0, $offset));
>>>>>>> my-saved-changes
    }
}
