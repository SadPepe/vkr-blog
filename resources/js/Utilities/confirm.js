export const confirm = (title, text, options = {}) => {
  return new Promise((resolve) => {
    const {
      confirmText = 'Подтвердить',
      cancelText = 'Отмена',
      onConfirm = () => {},
      onCancel = () => {}
    } = options

    const shouldProceed = window.confirm(`${title}\n\n${text}`)
    
    if (shouldProceed) {
      onConfirm()
      resolve(true)
    } else {
      onCancel()
      resolve(false)
    }
  })
}