-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server versie:                10.5.9-MariaDB-1:10.5.9+maria~focal - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Versie:              11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumpen data van tabel cc.organizations: ~2 rows (ongeveer)
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
REPLACE INTO `organizations` (`id`, `name`, `description`, `organizationOf`, `customerOf`, `accountManager`, `supportManager`, `technicalManager`, `projectManager`, `financialAccount`, `street`, `houseNumber`, `houseNumberExtension`, `zipCode`, `city`, `website`, `phoneNumber`, `logo`, `color`) VALUES
	(1, 'The Code Crowd', 'The Code Crowd is dé software ontwikkelaar van het MKB.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kerkpad', '2', NULL, '8071GG', 'Nunspeet', 'https://www.thecodecrowd.nl/', '+31341263273', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAACmAAAApgHdff84AAAFDklEQVRoge1aTWgTQRQepQ3GWrAVK9RSMTbk4C8qJBehIoJ69ST06s2fo968elWPXkVPeuhBRSkKCrag4k89BGtFSQtWmgpaIyoo33S/7dvJTLK7yQYN/aBMNrPZmTfz3ve+t9NVp+5d/6PaAKvbwQigQ14cz+1VA9091hvHZ9/rttC/VbeXno7p9uz+Q37/xOy0f237PfqTQsAQGDHU02cd6u3CnG7Nfl67+s3ft8SQ0tcF3aY7Umpz93r9eebrF1X5/VPNVxbVhnRX6AeXfyyqcmXRv54Xn5NAwJCbxee6zfb0qTOei9wsPvNX89i2naGnMDH7Xt1+9zrRyUu0TbC3J2tFgYudiHz/Vu2iyos9um1SiG2Ii52I3jVd+q9ViG3InelJ3R7N7LD2Ty3M+SSRNGOpRgwhI7kMgRErrBUDK6xVDwj0rCCEeSPTNxuJGQL6zXsCU3nkkGTMtI1rWQsrqOBdfQP6M6jz1VxJC0cCLpPt3aSvKr9+aonO/qwlv5Q84Qn0prvUBkt+qfz+5YvWOKhyrXx/Ro1szwe+mxnM6foDk4FwNCl3eEtOXXxyV/efsWR8TPLy0zE90UJ/xkrZyDusceKgyrVQXJmApEdBBXnPSWBykPjKC+xj2+z5ROmyoFOd3HMg9iTDILAjcAsMqrw65NqbcXWucERfw9VKYuvRB5c7XziqDTUrSwY3FmZ4MLckWYx65vT9G00zxBnsqEMw8SlHZYfY0K2IHRtgLGGLjWahLv1yF0oNBGIrUNcQKb9tjNQIZMXZaI5JLCGGgWSv/9oQV/zFQV1DwEZgMtBts9FI3jBR1xDQJ6rBqRbXF1HR/vXI8dw+zVKu2jzdmVpqO1I1B6BmU56UTwoB10J5ilhATCBbS92ExFbyJAkwsr2gyplF/42kmWfASJKVzDePwJXDJ/zPTddattc2kCtUuLe8fhrLSd5+N+kcBItz9cWj2JMMA6eMp3jELj34UAxIkVoyn0lTs11nqkrmt0zGYwJwKYrHIU9IYqfkO2E5gYHu9f5Owv3w8o67pbzSAG4zINwVsh6LRPfCrl54NKoX6OTuA/r62uS4dTwcT5ieU+VakNs0goB6xWRswL3oPziY070jO/IBI5RXBuB7ebTQm14XuIcv86iiXfU9xzNfqAcMwUP4QFj+8GPR7zN1FmLl6stlv6er7dq41L76XFIXHo/qVn5P4IjCdahkA8djYjbnEzBE7gTqDWwfiyfzbAT+jNhwyQz8Dqs6I5iO39vG4+S4kObBEMdjWWCmBWceCVtvRAWfZ7ofUe8wqezIRXUliuR2czuRDFn1fQ9pMO9b25HyX2AQMm6ivi+OrX6R+eWqPvxQrHk/Adcy4wWrDJfCbmz2g/1bpPnE1lrSCEwkqvwYkvFQXo4HM27CIrYhOCMkq+m3KJlw54sy6TEe6PdycaKeAtd1LSQ3m4xHUsJg2Z5NegJhT3xJIkoIzrflT1qXmS4XBQ3L+KisJgs07oC5+iZlh4HTECQ4yUrNgqmnbJOutThkTpOGjX8YWH4oZACMYUCGFXRkoGFPsrB18T8nDYO4QzYqB0vi3iFxwCqx2nzohPc/J0roH7jDg4+16ZW0yd+DfeD3ZCH5XLkLnLTcBdsuwUgagfncMcqGqmCHNMHqcQsxEH5EEcdDUNItDJQ+DkJAMiuIsxHzH2rwG5IDAp338Dn8DmNwPInx2ekqUbny/1r/FJRSfwGPm6+AIvLkRAAAAABJRU5ErkJggg==', '#6cbaa6');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
