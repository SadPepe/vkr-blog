// Относительное время для ленты и страницы поста: «только что», «12 минут назад»,
// «3 часа назад», «2 дня назад». Старше месяца — короткая дата без года.
// Точные дата и время остаются в title и в атрибуте datetime.

const MONTHS = ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];

const pluralize = (n, [one, few, many]) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
    return many;
};

// Дата и время по-русски, без года: 4 ноября, 14:20
export const formatDateTime = (value) => {
    const date = new Date(value);
    const pad = (n) => String(n).padStart(2, "0");
    return `${date.getDate()} ${MONTHS[date.getMonth()]}, ${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

export const relativeTime = (value) => {
    const date = new Date(value);
    const minutes = Math.floor((Date.now() - date.getTime()) / 60000);

    if (minutes < 1) return "только что";
    if (minutes < 60) return `${minutes} ${pluralize(minutes, ["минуту", "минуты", "минут"])} назад`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} ${pluralize(hours, ["час", "часа", "часов"])} назад`;

    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} ${pluralize(days, ["день", "дня", "дней"])} назад`;

    return `${date.getDate()} ${MONTHS[date.getMonth()]}`;
};
