Henhed_Piwik
============

*Henhed_Piwik* is a [Piwik][piwik] web analytics module for the
[Magento 2][magento] eCommerce platform.  Piwik is an extensible
free/libre analytics tool that can be self-hosted, giving you complete
data ownership.  Henhed_Piwik lets you integrate Piwik with your
Magento 2 store front.


Installation
------------

To install Henhed_Piwik, download and extract the
[master zip archive][download] and move the extracted folder to
*app/code/Henhed/Piwik* in your Magento 2 installation directory.

```sh
unzip magento2-henhed-piwik-master.zip
mkdir app/code/Henhed
mv magento2-henhed-piwik-master app/code/Henhed/Piwik
```

Alternatively, you can clone the Henhed_Piwik Git repository into
*app/code/Henhed/Piwik*.

```sh
git clone https://github.com/henkelund/magento2-henhed-piwik.git app/code/Henhed/Piwik
```

Finally, enable the module with the Magento CLI tool.

```sh
php bin/magento module:enable Henhed_Piwik
```

Configuration
-------------

Once intsalled, configuration options can be found in the Magento 2
administration panel under *Stores/Configuration/Sales/Piwik API*.
To start tracking, set *Enable Tracking* to *Yes*, enter the
*Hostname* of your Piwik installation and click *Save Config*.  If you
have multiple websites in the same Piwik installation, make sure the
*Site ID* configured in Magento is correct.

Disclaimer
----------

Henhed_Piwik is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU
Affero General Public License][agpl] for more details.

[piwik]: http://piwik.org/
    "Free Web Analytics Software"
[magento]: https://magento.com/
    "eCommerce Software & eCommerce Platform Solutions"
[agpl]: http://www.gnu.org/licenses/agpl.html
    "GNU Affero General Public License"
[download]: https://github.com/henkelund/magento2-henhed-piwik/archive/master.zip
    "magento2-henhed-piwik-master"
