/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2012 Fachhochschule Potsdam - http://fh-potsdam.de

Fritzing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fritzing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fritzing.  If not, see <http://www.gnu.org/licenses/>.

********************************************************************

$Revision: 6565 $:
$Author: irascibl@gmail.com $:
$Date: 2012-10-15 12:10:48 +0200 (Mon, 15 Oct 2012) $

********************************************************************/

#ifndef PALETTEMODEL_H
#define PALETTEMODEL_H

#include "modelpart.h"
#include "modelbase.h"

#include <QDomDocument>
#include <QList>
#include <QDir>
#include <QStringList>
#include <QHash>

class PaletteModel : public ModelBase
{
Q_OBJECT
public:
	PaletteModel();
	PaletteModel(bool makeRoot, bool doInit);
    ~PaletteModel();
	ModelPart * retrieveModelPart(const QString & moduleID);
	virtual bool containsModelPart(const QString & moduleID);
	virtual ModelPart * loadPart(const QString & path, bool update);
	void clear();
	bool loadedFromFile();
	QString loadedFrom();
	bool loadFromFile(const QString & fileName, ModelBase* referenceModel, bool checkViews);
	ModelPart * addPart(QString newPartPath, bool addToReference, bool updateIdAlreadyExists);
	void removePart(const QString &moduleID);
    void removeParts();
    QList<ModelPart *> search(const QString & searchText, bool allowObsolete);

	void clearPartHash();
	void setOrdererChildren(QList<QObject*> children);

protected:
	QHash<QString, ModelPart *> m_partHash;
	bool m_loadedFromFile;
	QString m_loadedFrom; // The file this was loaded from, only if m_loadedFromFile == true

	bool m_loadingContrib;
    bool m_fullLoad;

signals:
	void loadedPart(int i, int total);
    void incSearch();
    void addSearchMaximum(int);

protected:
	virtual void initParts();
	void loadParts();
	void loadPartsAux(QDir & dir, QStringList & nameFilters, int & loadedPart, int totalParts);
	void countParts(QDir & dir, QStringList & nameFilters, int & partCount);
    void search(ModelPart * modelPart, const QStringList & searchStrings, QList<ModelPart *> & modelParts, bool allowObsolete);

	void writeCommonBinsHeader();
	void writeCommonBinsHeaderAux(bool doIt, const QString &filename, const QString &binName);
	void writeCommonBinsFooter();
	void writeCommonBinsFooterAux(bool doIt, const QString &filename);
	void writeCommonBinInstance(const QString &moduleID, const QString &path, bool doIt, const QString &filename);
	void writeCommonBinAux(const QString &textToWrite, QIODevice::OpenMode openMode, bool doIt, const QString &filename);

protected:
	static bool CreateAllPartsBinFile;
	static QString AllPartsBinFilePath;
	static bool CreateNonCorePartsBinFile;
	static QString NonCorePartsBinFilePath;
	static bool CreateContribPartsBinFile;
	static QString ContribPartsBinFilePath;
	static bool CreateTempPartsBinFile;
	static QString TempPartsBinFilePath;

public:
	static void initNames();

};
#endif
